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
          <li class="breadcrumb-item active">Pick Instruction Configuration</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}
<div class="row mb-0">
    <div class="col-md-12 title-col">
        <div class="d-sm-flex justify-content-between">
            <h4 class="text-uppercase fontbold">Pick Instruction <span class="h5">(Detail Page Confiugration) </span></h4>

            @if(Auth::user()->role_id == 8)
            <div class="mb-0">
                <a href="#" class="btn button-st" data-toggle="modal" data-target="#addSettingModal">ADD Setting</a>
            </div>
            @endif
        </div>
    </div>
</div>
<div class="row entriestable-row mt-2">
    {{-- <div class="col-6">
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
                        <li data-id="{{$qc->id}}"  >
                            <div class="card card-primary pl-3 col-5 mt-1" style="cursor:grabbing">
                                <div class="card-body py-1">
                                    <input @if(!in_array($qc->id,$showedColumns)) checked @endif value="{{$qc->id}}" class="form-check-input orderable-checkbox" type="checkbox" id="id-{{$qc->id}}" />
                                    <label class="form-check-label" for="id-{{$qc->id}}">@if($qc->slug!=null){{$global_terminologies[$qc->slug]}} @else {{$qc->column_name}} @endif</label>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    @endif

                    <ul style="list-style:none">
                        Non Orderable Columns
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(10,$showedColumns)) checked @endif value="10" class="form-check-input orderable-checkbox" type="checkbox" id="id-10" />
                                    <label class="form-check-label" for="id-10">#{{$global_terminologies['qty']}} Ordered</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(11,$showedColumns)) checked @endif value="11" class="form-check-input orderable-checkbox" type="checkbox" id="id-11" />
                                    <label class="form-check-label" for="id-11">#{{$global_terminologies['qty']}} Sent</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(12,$showedColumns)) checked @endif value="12" class="form-check-input orderable-checkbox" type="checkbox" id="id-12" />
                                    <label class="form-check-label" for="id-12">#{{$global_terminologies['pieces']}} Ordered</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(13,$showedColumns)) checked @endif value="13" class="form-check-input orderable-checkbox" type="checkbox" id="id-13" />
                                    <label class="form-check-label" for="id-13">#{{$global_terminologies['pieces']}} Sent</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(14,$showedColumns)) checked @endif value="14" class="form-check-input orderable-checkbox" type="checkbox" id="id-14" />
                                    <label class="form-check-label" for="id-14">{{$global_terminologies['reference_price']}}</label>
                                </div>
                            </div>
                        </li>
						<li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(15,$showedColumns)) checked @endif value="15" class="form-check-input orderable-checkbox" type="checkbox" id="id-15" />
                                    <label class="form-check-label" for="id-15">{{$global_terminologies['default_price_type']}}</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(16,$showedColumns)) checked @endif value="16" class="form-check-input orderable-checkbox" type="checkbox" id="id-16" />
                                    <label class="form-check-label" for="id-16">{{$global_terminologies['default_price_type_wo_vat']}}</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(17,$showedColumns)) checked @endif value="17" class="form-check-input orderable-checkbox" type="checkbox" id="id-17" />
                                    <label class="form-check-label" for="id-17">Discount</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(18,$showedColumns)) checked @endif value="18" class="form-check-input orderable-checkbox" type="checkbox" id="id-18" />
                                    <label class="form-check-label" for="id-18">Vat</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(19,$showedColumns)) checked @endif value="19" class="form-check-input orderable-checkbox" type="checkbox" id="id-19" />
                                    <label class="form-check-label" for="id-19">{{$global_terminologies['unit_price_vat']}}</label>
                                </div>
                            </div>
                        </li>

                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(20,$showedColumns)) checked @endif value="20" class="form-check-input orderable-checkbox" type="checkbox" id="id-20" />
                                    <label class="form-check-label" for="id-20">{{$global_terminologies['total_amount_inc_vat']}}</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(21,$showedColumns)) checked @endif value="21" class="form-check-input orderable-checkbox" type="checkbox" id="id-21" />
                                    <label class="form-check-label" for="id-21">PO QTY</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(22,$showedColumns)) checked @endif value="22" class="form-check-input orderable-checkbox" type="checkbox" id="id-22" />
                                    <label class="form-check-label" for="id-22">PO No</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(23,$showedColumns)) checked @endif value="23" class="form-check-input orderable-checkbox" type="checkbox" id="id-23" />
                                    <label class="form-check-label" for="id-23">Customer Last Price</label>
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

    </div> --}}
    <div class="col-6">
        <div class="bg-white card">
            <h5 class="card-header">Pick Instructions Setting </h5>
            <div class="card-body">
                <div class="ml-2">
                    <p>Pick Instruction on Confirm </p>

                    <ul style="list-style:none">

                        <li>
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input @if($pi_config['pi_confirming_condition']==1) checked @endif value="1" type="radio" class="form-check-input " name="pi_confirming_condition">No validation
                                </label>
                            </div>
                        </li>
                        <li>
                              <div class="form-check">
                                <label class="form-check-label">
                                    <input @if($pi_config['pi_confirming_condition']==2) checked @endif value="2" type="radio" class="form-check-input "  name="pi_confirming_condition">Stock must be > 0
                                </label>
                            </div>
                        </li>
                        <li>
                              <div class="form-check">
                                <label class="form-check-label">
                                    <input @if($pi_config['pi_confirming_condition']==3) checked @endif value="3" type="radio" class="form-check-input " name="pi_confirming_condition">Available Quantity must be > 0
                                </label>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class=" float-right">
                    <button class="btn  btn-primary save-btn-pi-config">Save</button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6">
        <div class="bg-white card">
            <h5 class="card-header">Partial Pick Instructions Setting </h5>
            <div class="card-body">
                <div class="ml-2">
                    <p>Partial Pick Instruction on Confirm </p>
                    <ul style="list-style:none">
                        <li>
                              <div class="form-check">
                                <label class="form-check-label">
                                    <input {{($partial_pi_config != null ? ($partial_pi_config->display_prefrences == 1 ? 'checked' : '') : '')}} type="checkbox" class="form-check-input" id="partial_config" name="partial_config">Allow partial pick instruction.
                                </label>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class=" float-right">
                    <button class="btn  btn-primary save-btn-partial-pi-config">Save</button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 mt-4">
        <div class="bg-white card">
            <h5 class="card-header">Pick Instruction Redirection Settings </h5>
            <div class="card-body">
                <div class="ml-2">
                    <p>Pick Instruction Redirection on Confirm </p>
                    <ul style="list-style:none">
                        <li>
                              <div class="form-check">
                                <label class="form-check-label">
                                    <input {{($pi_redirection_config != null ? ($pi_redirection_config->display_prefrences == 1 ? 'checked' : '') : '')}} type="checkbox" class="form-check-input" id="redirection_config" name="redirection_config">Enable/Disable Pop-up For redirection.
                                </label>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class=" float-right">
                    <button class="btn  btn-primary save-btn-pi-redirection-config">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>



@endsection



@section('javascript')
    <script>
        $(document).on('click','.save-btn-pi-config',function(){
            $.ajax({
                url:"{{ route('pi-add-config') }}",
                data:{pi_confirming_condition:$("input[name='pi_confirming_condition']:checked").val()},
                method:'get',
                beforeSend:function(){
                    $('.save-btn-pi-config').prop('disabled',true);
                    $('.save-btn-pi-config').html('Please Wait...');
                    $('#loader_modal').modal({
                        backdrop: 'static',
                        keyboard: false
                        });
                    $("#loader_modal").modal('show');
                },
                success:function(result){
                    if (result.success === true) {
                        $('.save-btn-pi-config').prop('disabled',false);
                        $('.save-btn-pi-config').html('Save');
                        toastr.success('Success!', 'PI Confiugration updated successfully', {
                            "positionClass": "toast-bottom-right"
                        });
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }
                    else
                    toastr.error('Success!', 'Something went wrongf', {
                            "positionClass": "toast-bottom-right"
                    });
                    $("#loader_modal").modal('hide');
                },
                error:function(){
                    $('.save-btn-pi-config').prop('disabled',false);
                    $('.save-btn-pi-config').html('Save');
                     toastr.error('Success!', 'Something went wrongf', {
                            "positionClass": "toast-bottom-right"
                    });
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            })
        });

        $(document).on('click','.save-btn-partial-pi-config',function(){
            var partial_config = 'true';
            var isChecked = $("#partial_config").is(":checked");
            if (isChecked) {
                partial_config = 'true';
            } else {
                partial_config = 'false';
            }
            $.ajax({
                url:"{{ route('partial-pi-add-config') }}",
                data:{partial_config:partial_config},
                method:'get',
                beforeSend:function(){
                    $('.save-btn-partial-pi-config').prop('disabled',true);
                    $('.save-btn-partial-pi-config').html('Please Wait...');
                    $('#loader_modal').modal({
                        backdrop: 'static',
                        keyboard: false
                        });
                    $("#loader_modal").modal('show');
                },
                success:function(result){
                    $('.save-btn-partial-pi-config').prop('disabled',false);
                    $('.save-btn-partial-pi-config').html('Save');
                    if (result.success === true) {
                        toastr.success('Success!', 'Partial PI Confiugration updated successfully', {
                            "positionClass": "toast-bottom-right"
                        });
                        // setTimeout(() => {
                        //     window.location.reload();
                        // }, 1500);
                    }
                    $("#loader_modal").modal('hide');
                },
                error:function(){
                    $('.save-btn-partial-pi-config').prop('disabled',false);
                    $('.save-btn-partial-pi-config').html('Save');
                    toastr.error('Success!', 'Something went wrongf', {
                        "positionClass": "toast-bottom-right"
                    });
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            })
        });

        $(document).on('click','.save-btn-pi-redirection-config',function(){
            var redirection_config = 'true';
            var isChecked = $("#redirection_config").is(":checked");
            if (isChecked) {
                redirection_config = 'true';
            } else {
                redirection_config = 'false';
            }
            $.ajax({
                url:"{{ route('pi-redirection-add-config') }}",
                data:{redirection_config:redirection_config},
                method:'get',
                beforeSend:function(){
                    $('.save-btn-pi-redirection-config').prop('disabled',true);
                    $('.save-btn-pi-redirection-config').html('Please Wait...');
                    $('#loader_modal').modal({
                        backdrop: 'static',
                        keyboard: false
                        });
                    $("#loader_modal").modal('show');
                },
                success:function(result){
                    $('.save-btn-pi-redirection-config').prop('disabled',false);
                    $('.save-btn-pi-redirection-config').html('Save');
                    if (result.success === true) {
                        toastr.success('Success!', 'PI Redirection Confiugration updated successfully', {
                            "positionClass": "toast-bottom-right"
                        });
                        // setTimeout(() => {
                        //     window.location.reload();
                        // }, 1500);
                    }
                    $("#loader_modal").modal('hide');
                },
                error:function(){
                    $('.save-btn-pi-redirection-config').prop('disabled',false);
                    $('.save-btn-pi-redirection-config').html('Save');
                    toastr.error('Success!', 'Something went wrongf', {
                        "positionClass": "toast-bottom-right"
                    });
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            })
        });
    </script>
@endsection
