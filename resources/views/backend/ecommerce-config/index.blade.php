@extends('backend.layouts.layout')
@section('title','Search Config')
@section('content')


<style type="text/css">
    .datepicker-container {z-index: 1100 !important;}
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
          <li class="breadcrumb-item active">E-Commerce Configuration</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}
<div class="row mb-0">
    <div class="col-md-12 title-col">
        <div class="d-sm-flex justify-content-between">
            <h4 class="text-uppercase fontbold">E-commerce Configuration<span class="h5">  (Confiugration)</span></h4>
            <a href="javascript:void(0);" class="btn btn-primary add-holidays">Add Holidays</a>
        </div>
    </div>
</div>
<div class="row entriestable-row mt-2">
    <div class="col-6">
        <div class="bg-white card">
            <h5 class="card-header">Enable E-commerce Module</h5>
            <div class="card-body">
                <div class="ml-2">
                      <table class="table table-responsive headings-color sales-customer-table dataTable const-font" style="width: 100%;">
                            <tr >   
                            <td class="fontbold">
                                <label class="form-check-label" for="ecommerce">
                                    E-commerce Enabled
                                </label>
                            </td>
                            <td >
                            
                            &nbsp &nbsp  &nbsp
                                <input class="form-check-input global-access-search" type="checkbox" value="ecommerce" id="ecommerce" name="ecommerce" @if(@$search_array_config['status'][0] == 1) checked  @endif }} />
                            </td>
                            </tr>
                               
                           <tr> 
                                <td class="fontbold">
                                    <p>Order From Other Origin</p>
                                </td>
                                <td >
                                     <input type="radio" id="order_from_other_region" name="order_from_other_region" value="2"
                                     @if(@$search_array_config['status'][3] == 2) checked @endif >

                                     <label class="form-radio-label font-bold" >
                                         Not allowed
                                    </label>
                                    &nbsp &nbsp  &nbsp
                                     <input type="radio" id="order_from_other_region" name="order_from_other_region" value="1"  @if(@$search_array_config['status'][3] == 1) checked @endif >
                                     <label class="form-radio-label font-bold" >
                                        Default Shipment
                                    </label>

                                 </td>
                                
                            </tr>

                            <tr>
                                    <td class="fontbold">
                                    <label class="form-check-label " for="ecommerce">Enabled Free Shipment</label>
                                    </td>
                                    
                                    <td>&nbsp &nbsp  &nbsp
                                    <input class="form-check-input global-access-search" type="checkbox" 
                                    id="enable_free_shippment" name="enable_free_shipment" value="enable_free_shipment" @if(@$search_array_config['status'][1] == 1) checked @endif >
                                    </td>
                                </tr>
                                @if(@$search_array_config['status'][1] == '1')
                                    @php $show_shipment = "";  @endphp
                                @else
                                    @php $show_shipment = "display: none"; @endphp
                                @endif
                            <tr id="hiddenfield" style="{{$show_shipment}}">
                                <td class="fontbold">
                                    <label class="form-check-label " for="ecommerce">
                                    Free shipment after</label>
                                    </td>

                                <td>
                                    <input class="" type="number" style="width:84%; height:33px;" name="free_shipment_amount" id="free_shipment_amount" placeholder="Enter amount" value="{{@$search_array_config['status'][4]}}">
                                    </td>
                            </tr>
                                
                            <tr>   
                                    <td class="fontbold">
                                        <label class="form-check-label " for="ecommerce">Email Notification</label>
                                    </td>
                                    <td >
                                    &nbsp &nbsp  &nbsp  
                                    <input  id="email_for_sending_billing_info"  class="form-check-input  global-access-search" type="checkbox" name="email_notification" value="email_notifications" @if(@$search_array_config['status'][2] == 1)checked @endif>
                                    </td>
                            </tr>
                            <tr>
                            <td class="fontbold">
                            <label class="form-check-label" style="font-size:14px;">Warehouses</label>
                            </td>
                            <td>
                            <select class=""  style="font-size:16px; height:35px; width:90%;" class="block  w-full" id="warehouses_selection_list" name="       warehouses_selection_list" required>
                                <option value="" selected="" disabled>Select </option>
                                @foreach($warehouses as $warehouse)
                                <option value="{{$warehouse->id}}" @if($warehouse->id == @$search_array_config['status'][5]) Selected @endif> {{$warehouse->warehouse_title}} </option>
                                 @endforeach
                            </select>
                            </td>
                        </tr>  

                        <tr>
                            <td class="fontbold">
                                <label class="form-check-label" style="font-size:14px;">Pricing</label>
                            </td>
                            <td>
                            <select class=""  style="font-size:16px; height:35px; width:90%;" class="block  w-full" id="defualt_cat" name="defualt_cat" required>

                                <option value="" selected="" disabled>Select </option>
                                @foreach($customer_cat as $cat)
                                <option value="{{$cat->id}}" @if($cat->id == @$search_array_config['status'][6]) Selected @endif> {{$cat->title}} 
                                </option>
                                 @endforeach
                            </select>
                            </td>
                        </tr>    
                        <tr>
                            <td class="fontbold">Sales Person</td>
                            <td>
                                 <select class=""  style="font-size:16px; height:35px; width:90%;" class="block  w-full" id="sales_person" name="sales_person" required>
                                <option value="" selected="" disabled>Select </option>
                                 @foreach($users as $user)
                                <option value="{{$user->id}}" @if(@$user->id == @$search_array_config['status'][7]) Selected @endif > {{$user->name}}
                                </option>
                                 @endforeach
                                </select>

                            </td>

                        </tr>          
                         <tr>
                            <td class="fontbold">Currency</td>
                            <td>
                                 <select class=""  style="font-size:16px; height:35px; width:90%;" class="block  w-full" id="currency_select" name="currency_select" required>
                                <option value="" selected="" disabled>Select </option>
                                 @foreach($currencies as $currency)
                                <option value="{{$currency->id}}" @if(@$currency->id == @$search_array_config['status'][8]) Selected @endif > {{$currency->currency_name}}
                                </option>
                                 @endforeach
                                </select>

                            </td>

                        </tr>   
                      <tr>
                            <td class="fontbold" style="display: none;" >Schain URL</td>
                            <td><input type="hidden" name="schain_url" id="schain_url" style="width:85%; height:33px;" value="{{@$search_array_config['status'][9]}}"></td>
                        </tr>
                        <tr> 
                            <td class="fontbold" style="display: none;">Ecomm Url</td>
                            <td><input type="hidden" name="ecom_url" id="ecom_url" style="width:85%; height:33px;" value="{{@$search_array_config['status'][10]}}"></td>

                        </tr>          
                            
                      </table>
                </div>
                <div class=" float-right">
                    <button class="btn  btn-primary save-btn-search-config">Save</button>
                </div>
            </div>

        </div>
    </div>

    <div class="col-6">
        <div class="bg-white card">
            <h5 class="card-header">E-commerce Holidays Management</h5>
            <div class="card-body">
                <div class="ml-2">
                    <table class="table entriestable table-bordered table-product text-center ecommerce-holidays" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>SR.#</th>
                                <th>Holiday Date</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Holiday add modal -->
<div class="modal" id="holidays_modal" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Holiday</h4>
                <button type="button" class="close close-btn" data-dismiss="modal">&times;</button>
            </div>
            <form role="form" id="add-holiday-form" class="add-holiday-form" method="post">
                @csrf
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-8">
                            <label class="mt-3 font-weight-bold">Date:</label>
                            <input type="text" name="holiday_date" class="form-control holidays" placeholder="Choose Date" required="" readonly="">
                        </div>
                    </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-primary add-holidays-btn" id="add-holidays-btn">Add</button>
                  </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!--  Content End Here -->
@endsection

@section('javascript')
{{-- <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script> --}}
<script type="text/javascript">

    $(document).ready(function () {
        $(".holidays").datepicker({
            format: "dd/mm/yyyy",
            autoHide: true,
        });
    });

    $('.save-btn-search-config').on('click', function() {
        var menus = [];
        var menu_stat = [];
         
         
        $.each($('.global-access-search:not(:checked), .global-access-search:checked'), function() {
            menus.push($(this).val());
            
             if($("#ecommerce").prop("checked") == false)
            {

               menu_stat.push(null); 
            }
            else if($(this).prop("checked") == true)
            {
                menu_stat.push(1);
            }
            else if($(this).prop("checked") == false)
            {
                menu_stat.push(0);
            }
             
            
        });
        // $.each($('#email_for_sending_billing_info:not(:checked), #email_for_sending_billing_info:checked'),function() {
        //     menus.push('mail_notification');
        //     if($(this).prop("checked") == true)
        //     {
        //         menu_stat.push(1);
        //     }
        //     else if($(this).prop("checked") == false)
        //     {
        //         menu_stat.push(0);
        //     }
            
        // });
        $.each($('#enable_free_shipment:not(:checked), #enable_free_shipment:checked'),function() {
            menus.push('enable_free_shipment');
         if($("#ecommerce").prop("checked") == false)
            {

               menu_stat.push(null); 
            }
         else if($(this).prop("checked") == true)
            {
                menu_stat.push(1);
            }
         else if($(this).prop("checked") == false)
            { 
                menu_stat.push(0);
            }
        });
        if($("#ecommerce").prop('checked')==true){
           
            menus.push('order_from_other_region');
            menu_stat.push($("input[name=order_from_other_region]:checked").val());

            menus.push('free_shipment_after');
        if(document.getElementById('enable_free_shippment').checked == true){
            menu_stat.push($("#free_shipment_amount").val());    
        }else{
            menu_stat.push('');
        }
        
        

            menus.push('default_warehouse');
            menu_stat.push($('#warehouses_selection_list').val());
            menus.push('defualt_cat');
            menu_stat.push($('#defualt_cat').val());
            menus.push('sales_person');
            menu_stat.push($('#sales_person').val());
            menus.push('currency_select');
            menu_stat.push($('#currency_select').val());

            //new fields to be added
             menus.push('schain_url');
            menu_stat.push($('#schain_url').val());
             menus.push('ecom_url');
            menu_stat.push($('#ecom_url').val());


            }
        else{
            menus.push('order_from_other_region');
            menu_stat.push(null);
            menus.push('free_shipment_after');
            menu_stat.push(null);
            menus.push('default_warehouse');
            menu_stat.push(null);
            menus.push('defualt_cat');
            menu_stat.push(null);
            menus.push('sales_person');
            menu_stat.push(null);
            menus.push('currency_select');
            menu_stat.push(null);
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.ajax({
            url: "{{ route('update-ecommerce-config-columns') }}",
            method: 'get',
            data: {
                menus : menus,
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
                    toastr.success('Success!', 'Ecommerce Setting Updated Successfully', {
                      "positionClass": "toast-bottom-right"
                    });
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                }
                else
                {
                $("#loader_modal").modal('hide');
                }
            },
            error: function(request, status, error)
            {
               $("#loader_modal").modal('hide');
            }
        });
    });
        
    $(document).ready(function(e)
    {
        $(document).on("change","#enable_free_shippment",function(e) 
        {
            if($("#enable_free_shippment").prop( "checked")==true)
            {
               $("#hiddenfield").show();
            }
            else
            {
               $("#free_shipment_amount").val(null);
               $("#hiddenfield").fadeOut();
            }                                          
        });

        $("#warehouses_selection_list").click(function(e){
           $("#disabled_option").hide();             
        });

        var table2 = $('.ecommerce-holidays').DataTable({
            "sPaginationType": "listbox",
            processing: false,
            "language": {
                processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '
            },
            ordering: false,
            searching:false,
            serverSide: true,
            scrollX: true,
            scrollY : '90vh',
            scrollCollapse: true,
            "lengthMenu": [100,200,300,400],
            ajax: 
            {
                beforeSend: function(){
                    $('#loader_modal').modal('show');
                },
                url: "{!! route('get-all-holidays-data') !!}",
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                { data: 'holiday_date', name: 'holiday_date' },
            ],
            drawCallback: function(){
                $('#loader_modal').modal('hide');
            },
        });

        $(document).on('click','.add-holidays', function(){
            $("#holidays_modal").modal('show');
        });

        $(document).on('submit', '#add-holiday-form', function(e){
            var value_of_date = $('.holidays').val();
            if(value_of_date == '')
            {
                toastr.error('Error!', 'Please choose date first !!!',{"positionClass": "toast-bottom-right"});
                return false;
            }

            e.preventDefault();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{ route('add-ecom-holidays') }}",
                method: 'post',
                data: $('#add-holiday-form').serialize(),
                beforeSend: function(){
                    $('.add-holidays-btn').html('Please wait...');
                    $('.add-holidays-btn').addClass('disabled');
                    $('.add-holidays-btn').attr('disabled', true);
                },
                success: function(result){
                    $('.add-holidays-btn').html('ADD');
                    $('.add-holidays-btn').attr('disabled', false);
                    $('.add-holidays-btn').removeAttr('disabled');
                    $('.add-holidays-btn').removeClass('disabled');
                    if(result.success === true)
                    {
                        toastr.success('Success!', 'Holiday added successfully',{"positionClass": "toast-bottom-right"});
                        $('#holidays_modal').modal('hide');
                        $('.ecommerce-holidays').DataTable().ajax.reload();
                        $('#add-holiday-form')[0].reset();
                    }
                    else
                    {
                        $('#add-holiday-form')[0].reset();
                        toastr.error('Error!', 'Already exist, please choose different date !!!',{"positionClass": "toast-bottom-right"});
                    }
                },
                error: function (request, status, error) {
                    $('#loader_modal').modal('hide');
                    $('.add-holidays-btn').html('ADD');
                    $('.add-holidays-btn').attr('disabled', false);
                    $('.add-holidays-btn').removeClass('disabled');
                    $('.add-holidays-btn').removeAttr('disabled');
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
    });

    
</script>
@endsection
