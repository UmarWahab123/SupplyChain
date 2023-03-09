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
          <li class="breadcrumb-item active">PO Configuration</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}
<div class="row mb-0">
    <div class="col-md-12 title-col">
        <div class="d-sm-flex justify-content-between">
            <h4 class="text-uppercase fontbold">PURCHASE ORDER <span class="h5">(Detail Page Confiugration) </span></h4>

            @if(Auth::user()->role_id == 8)
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
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(10,$showedColumns)) checked @endif value="10" class="form-check-input orderable-checkbox" type="checkbox" id="id-10" />
                                    <label class="form-check-label" for="id-10">Gross Weight</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(11,$showedColumns)) checked @endif value="11" class="form-check-input orderable-checkbox" type="checkbox" id="id-11" />
                                    <label class="form-check-label" for="id-11">Order Qty</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(12,$showedColumns)) checked @endif value="12" class="form-check-input orderable-checkbox" type="checkbox" id="id-12" />
                                    <label class="form-check-label" for="id-12">Avg Order Quantity (/ Billed Unit)</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(13,$showedColumns)) checked @endif value="13" class="form-check-input orderable-checkbox" type="checkbox" id="id-13" />
                                    <label class="form-check-label" for="id-13">QTY</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(14,$showedColumns)) checked @endif value="14" class="form-check-input orderable-checkbox" type="checkbox" id="id-14" />
                                    <label class="form-check-label" for="id-14">PCS</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(15,$showedColumns)) checked @endif value="15" class="form-check-input orderable-checkbox" type="checkbox" id="id-15" />
                                    <label class="form-check-label" for="id-15">QTY Inv</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(16,$showedColumns)) checked @endif value="16" class="form-check-input orderable-checkbox" type="checkbox" id="id-16" />
                                    <label class="form-check-label" for="id-16">Unit Price</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(19,$showedColumns)) checked @endif value="19" class="form-check-input orderable-checkbox" type="checkbox" id="id-19" />
                                    <label class="form-check-label" for="id-19">Price Date</label>
                                </div>
                            </div>
                        </li>
                        <!--  <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(18,$showedColumns)) checked @endif value="18" class="form-check-input orderable-checkbox" type="checkbox" id="id-18" />
                                    <label class="form-check-label" for="id-18">Amount</label>
                                </div>
                            </div>
                        </li> -->
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(20,$showedColumns)) checked @endif value="20" class="form-check-input orderable-checkbox" type="checkbox" id="id-20" />
                                    <label class="form-check-label" for="id-20">Discount</label>
                                </div>
                            </div>
                        </li>

                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(23,$showedColumns)) checked @endif value="23" class="form-check-input orderable-checkbox" type="checkbox" id="id-23" />
                                    <label class="form-check-label" for="id-23">Order #</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(24,$showedColumns)) checked @endif value="24" class="form-check-input orderable-checkbox" type="checkbox" id="id-24" />
                                    <label class="form-check-label" for="id-24">Total Gross Weight</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(17,$showedColumns)) checked @endif value="17" class="form-check-input orderable-checkbox" type="checkbox" id="id-17" />
                                    <label class="form-check-label" for="id-17">Purchasing VAT</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(18,$showedColumns)) checked @endif value="18" class="form-check-input orderable-checkbox" type="checkbox" id="id-18" />
                                    <label class="form-check-label" for="id-18">Unit Price (+Vat)</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(21,$showedColumns)) checked @endif value="21" class="form-check-input orderable-checkbox" type="checkbox" id="id-21" />
                                    <label class="form-check-label" for="id-21">Total Amount (W/O Vat)</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(22,$showedColumns)) checked @endif value="22" class="form-check-input orderable-checkbox" type="checkbox" id="id-22" />
                                    <label class="form-check-label" for="id-22">Total Amount (Inv Vat)</label>
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
   

</div>


<!--  Content End Here -->


@endsection



@section('javascript')
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
    var value = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17,18,19,20,21];
    var index = [0, 1, 2, 3, 4, 5, 6, 7, 8,9,22];
    var ids = [0, 1, 2, 3, 4, 5, 6, 7, 8,9,22];
    var update = false;
    console.log(index);
    
    $(function() {
        var listId = null;
        $("#sortable").sortable({
            update: function(event, ui) {
                update = true;
                index = [];
                value = [];
                ids = [];
                $('#sortable li').each(function(i, el) {
                    value.push($(this).data("id"));
                    index.push(i);
                    ids.push($(this).data("id"));
                });

                value.push(10,11, 12, 13, 14, 15, 16, 17,18,19,20,21);
            },

        });
        $("#sortable").disableSelection();

        $('.po_vat_configuration_form').on("submit",function(e){
            e.preventDefault();
            $.ajaxSetup({
              headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
              }
            });
            $.ajax({
              url: "{{route('save-po-vat-configuration')}}",
              method: 'post',
              data: new FormData(this),
              contentType: false,
              cache: false,
              processData:false,
              beforeSend: function(){
                $('.purchasing_vat_btn').val('Loading..');
                $('.purchasing_vat_btn').addClass('disabled');
                $('.purchasing_vat_btn').attr('disabled', true);
              },
              success: function(result){
                if(result.success == true)
                {
                    toastr.success('Success!', 'PO Confiugration updated successfully', {
                        "positionClass": "toast-bottom-right"
                    });
                }
                location.reload();
              },
              error: function (request, status, error) {
               
              }
            });
        });
    });

    $('.save-btn').on('click', function() {
        var columns = [];
        $.each($('.orderable-checkbox'), function() {
            if ($(this).prop("checked") == false) {
                columns.push($(this).val());
            }
        });
        $.ajax({
            url: "{{ route('po-config-add') }}",
            method: 'get',
            data: {
                columns: columns,
                value: value,
                index: index,
                ids: ids,
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
                    toastr.success('Success!', 'PO Confiugration updated successfully', {
                        "positionClass": "toast-bottom-right"
                    });
                    setTimeout(() => {
                    	window.location.reload();
                    }, 1500);
                }
                else{
                    $("#loader_modal").modal('hide');
                }
            },
            error: function(request, status, error){
                $("#loader_modal").modal('hide');
            }
        });
    });





    
</script>
@endsection
