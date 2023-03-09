@extends('backend.layouts.layout')
@section('title','Search Config')
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
          <li class="breadcrumb-item active">Search Configuration</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}
<div class="row mb-0">
    <div class="col-md-12 title-col">
        <div class="d-sm-flex justify-content-between">
            <h4 class="text-uppercase fontbold">Orders, Purchase Orders, Transfer Documents, Credit Note & Debit Note<span class="h5">  (Confiugration)</span></h4>
        </div>
    </div>
</div>
<div class="row entriestable-row mt-2">
    <div class="col-6">
        <div class="bg-white card">
            <h5 class="card-header">Search Columns</h5>
            <div class="card-body">
                <div class="ml-2">
                    <ul style="list-style:none">
                        <li>
                            <div class="form-check custom-checkbox custom-control">
                                <input class="form-check-input global-access-search" type="checkbox" value="prod_code" id="prod_code" {{ @$search_array_config == null ? 'checked' : (@$search_array_config['status'][0] == '1' ? 'checked' : '')}} />
                                <label class="form-check-label" for="prod_code">Product Code</label>
                            </div>
                        </li>
                        <li>
                            <div class="form-check custom-checkbox custom-control">
                                <input class="form-check-input global-access-search" type="checkbox" value="prod_desc" id="prod_desc" {{ @$search_array_config == null ? 'checked' : (@$search_array_config['status'][1] == '1' ? 'checked' : '')}} />
                                <label class="form-check-label" for="prod_desc">Product Description</label>
                            </div>
                        </li>
                        <li>
                            <div class="form-check custom-checkbox custom-control">
                                <input class="form-check-input global-access-search" type="checkbox" value="brand" id="brand" {{@$search_array_config == null ? 'checked' : (@$search_array_config['status'][2] == '1' ? 'checked' : '')}} />
                                <label class="form-check-label" for="brand">Brand</label>
                            </div>
                        </li>
                        <li>
                            <div class="form-check custom-checkbox custom-control">
                                <input class="form-check-input global-access-search" type="checkbox" value="sup_ref_name" id="sup_ref_name" {{@$search_array_config == null ? 'checked' : (@$search_array_config['status'][3] == '1' ? 'checked' : '')}} />
                                <label class="form-check-label" for="sup_ref_name">Supplier</label>
                            </div>
                        </li>
                        <li>
                            <div class="form-check custom-checkbox custom-control">
                                <input class="form-check-input global-access-search" type="checkbox" value="sup_ref_no" id="sup_ref_no" {{@$search_array_config == null ? 'checked' : (@$search_array_config['status'][4] == '1' ? 'checked' : '')}} />
                                <label class="form-check-label" for="sup_ref_no">Supplier Ref#</label>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class=" float-right">
                    <button class="btn  btn-primary save-btn-search-config">Save</button>
                </div>
            </div>

        </div>
    </div>

    <div class="col-6">
        <div class="bg-white card">
            <h5 class="card-header">Hide/Show Columns</h5>
            <div class="card-body">
                <div class="ml-2">
                    <ul style="list-style:none">
                        <li>
                            <div class="form-check custom-checkbox custom-control">
                                <input class="form-check-input global-access-hide" type="checkbox" value="sup_ref_no" id="sup_ref_no" {{ @$search_array == null ? 'checked' : (@$search_array['status'][0] == '1' ? 'checked' : '')}} />
                                <label class="form-check-label" for="sup_ref_no">Sup Ref #</label>
                            </div>
                        </li>
                        <li>
                            <div class="form-check custom-checkbox custom-control">
                                <input class="form-check-input global-access-hide" type="checkbox" value="prod_code" id="prod_code" {{ @$search_array == null ? 'checked' : (@$search_array['status'][1] == '1' ? 'checked' : '')}} />
                                <label class="form-check-label" for="prod_code">Product Code</label>
                            </div>
                        </li>
                        <li>
                            <div class="form-check custom-checkbox custom-control">
                                <input class="form-check-input global-access-hide" type="checkbox" value="sup_ref_name" id="sup_ref_name" {{ @$search_array == null ? 'checked' : (@$search_array['status'][2] == '1' ? 'checked' : '')}} />
                                <label class="form-check-label" for="sup_ref_name">Supplier</label>
                            </div>
                        </li>
                        <li>
                            <div class="form-check custom-checkbox custom-control">
                                <input class="form-check-input global-access-hide" type="checkbox" value="brand" id="brand" {{ @$search_array == null ? 'checked' : (@$search_array['status'][3] == '1' ? 'checked' : '')}} />
                                <label class="form-check-label" for="brand">Brand</label>
                            </div>
                        </li>
                        <li>
                            <div class="form-check custom-checkbox custom-control">
                                <input class="form-check-input global-access-hide" type="checkbox" value="prod_desc" id="prod_desc" {{ @$search_array == null ? 'checked' : (@$search_array['status'][4] == '1' ? 'checked' : '')}} />
                                <label class="form-check-label" for="prod_desc">Product Description</label>
                            </div>
                        </li>
                        <li>
                            <div class="form-check custom-checkbox custom-control">
                                <input class="form-check-input global-access-hide" type="checkbox" value="prod_type" id="prod_type" {{ @$search_array == null ? 'checked' : (@$search_array['status'][5] == '1' ? 'checked' : '')}} />
                                <label class="form-check-label" for="prod_type">Type</label>
                            </div>
                        </li>
                        <li>
                            <div class="form-check custom-checkbox custom-control">
                                <input class="form-check-input global-access-hide" type="checkbox" value="prod_note" id="prod_note" {{ @$search_array == null ? 'checked' : (@$search_array['status'][6] == '1' ? 'checked' : '')}} />
                                <label class="form-check-label" for="prod_note">Note</label>
                            </div>
                        </li>
                        <li>
                            <div class="form-check custom-checkbox custom-control">
                                <input class="form-check-input global-access-hide" type="checkbox" value="rsv" id="rsv" {{ @$search_array == null ? 'checked' : (@$search_array['status'][7] == '1' ? 'checked' : '')}} />
                                <label class="form-check-label" for="rsv">Rsv</label>
                            </div>
                        </li>
                        <li>
                            <div class="form-check custom-checkbox custom-control">
                                <input class="form-check-input global-access-hide" type="checkbox" value="stock" id="stock" {{ @$search_array == null ? 'checked' : (@$search_array['status'][8] == '1' ? 'checked' : '')}} />
                                <label class="form-check-label" for="stock">Stock</label>
                            </div>
                        </li>
                        <li>
                            <div class="form-check custom-checkbox custom-control">
                                <input class="form-check-input global-access-hide" type="checkbox" value="available" id="available" {{ @$search_array == null ? 'checked' : (@$search_array['status'][9] == '1' ? 'checked' : '')}} />
                                <label class="form-check-label" for="available">Available</label>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class=" float-right">
                    <button class="btn  btn-primary save-btn-hide-config">Save</button>
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
    //Hide and show column
    $('.save-btn-hide-config').on('click', function() {
        
        var menus = [];
        var menu_stat = [];
        
        $.each($('.global-access-hide:not(:checked), .global-access-hide:checked'), function() {
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

        var removeItem = 0;
        var check = jQuery.grep(menu_stat, function(value) {
          return value != removeItem;
        });

        if(check.length < 2)
        {
            toastr.error('Error!', 'Minimum of 2 columns must be selected !!!', { "positionClass": "toast-bottom-right" });
            return false;
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.ajax({
            url: "{{ route('update-search-config') }}",
            method: 'get',
            data: {
                menus: menus,
                menu_stat: menu_stat,
            },
            beforeSend: function() {
                $('.save-btn-hide-config').text('Please wait...');
                $('.save-btn-hide-config').attr('disabled', true);
                $('#loader_modal').modal({
                    backdrop: 'static',
                    keyboard: false
                    });
                $("#loader_modal").modal('show');
            },
            success: function(result) {
                $('.save-btn-hide-config').text('Save');
                $('.save-btn-hide-config').removeAttr('disabled');
                if (result.success === true) 
                {
                    toastr.success('Success!', 'Search Setting Updated Successfully', {
                        "positionClass": "toast-bottom-right"
                    });
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                }else{
                    $("#loader_modal").modal('hide');
                }
            },
            error: function(request, status, error){
                $("#loader_modal").modal('hide');
            }
        });
    });

    //search column
    $('.save-btn-search-config').on('click', function() {
        var menus = [];
        var menu_stat = [];
        
        $.each($('.global-access-search:not(:checked), .global-access-search:checked'), function() {
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


        var removeItem = 0;
        var check = jQuery.grep(menu_stat, function(value) {
          return value != removeItem;
        });

        if(check.length < 2)
        {
            toastr.error('Error!', 'Minimum of 2 columns must be selected !!!', { "positionClass": "toast-bottom-right" });
            return false;
        }


        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.ajax({
            url: "{{ route('update-search-config-columns') }}",
            method: 'get',
            data: {
                menus: menus,
                menu_stat: menu_stat,
            },
            beforeSend: function() {
                $('.save-btn-search-config').text('Please wait...');
                $('.save-btn-search-config').attr('disabled', true);
                $('#loader_modal').modal({
                    backdrop: 'static',
                    keyboard: false
                    });
                $("#loader_modal").modal('show');
            },
            success: function(result) {
                $('.save-btn-search-config').text('Save');
                $('.save-btn-search-config').removeAttr('disabled');
                if (result.success === true) 
                {
                    toastr.success('Success!', 'Search Setting Updated Successfully', {
                        "positionClass": "toast-bottom-right"
                    });
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
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
