@extends('users.layouts.layout')

@section('title','Products Management | Supply Chain')
<style>
  .customer__dropdown > .select2-container--default .select2-selection--single {
    border: 1px solid red !important;
  }
  
</style>
@section('content')

<div class="row">
  <div class="col-md-12">
    <a href="{{ url()->previous() }}" class="float-left pt-3">
    <span class="vertical-icons" title="Back">
    <img src="{{asset('public/icons/back.png')}}" width="27px">
    </span>
    </a>
    <ol class="breadcrumb" style="background-color:transparent; font-size: 20px; color: blue !important;">
        @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 3 || Auth::user()->role_id == 4 || Auth::user()->role_id == 11)
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
          <li class="breadcrumb-item active">Stock Adjustments</li>
      </ol>
  </div>
</div>

<div class="row mb-3">
  <div class="col-md-8 title-col">
    <h3 class="maintitle">Stock Adjustments</h3>
  </div>
</div>

<div class="row joberrormsgDiv mt-2 d-none">
    <div class="container" style="max-width: 100% !important; min-width: 100% !important">
      <div class="alert alert-danger alert-dismissible">
        <a href="javascript:void(0)" class="closeErrorDiv">&times;</a>
        <span class="job-errors"></span>
      </div>
    </div>
  </div>
<?php if(!empty($errors) && count($errors)>0) : ?>
<div class="row errormsgDiv">
  <div class="container">
    <div class="alert alert-danger alert-dismissible">
      <a href="javascript:void(0)" class="closeErrorDiv">&times;</a>
      <?php foreach ($errors->all() as $error) : ?>
        <span><?php echo $error ?></span>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>
<div class="error_div d-none">
  <div class="container">
    <div class="alert alert-danger alert-dismissible">
      <a href="javascript:void(0)" class="closeErrorDiv">&times;</a>
      <div class="error_msg_div">

      </div>
    </div>
  </div>
</div>

<div class="row mb-3 justify-content-center ">
  <div class="col-lg-12 col-md-12 col-12 signform-col">
    <div class="row add-gemstone">
      <div class="col-md-12">
        <div class="bg-white pr-4 pl-4 pt-4 pb-5">

          <ul class="nav nav-tabs">
            <li class="nav-item ">
              <a class="nav-link cut-tab active" data-toggle="tab" href="#tab1">Stock Adjustments</a>
            </li>
          </ul>

          <div class="tab-content mt-3">
            <div class="tab-pane active" id="tab1">
              <form method="POST" id="filteredProducts">
                {{csrf_field()}}
              <div class="form-group row filters_div">
                <div class="col">
                  <div class="form-group">
                    <label class="text-nowrap stock-lable">Choose Warehouse</label>
                    <select class="form-control selecting-warehouses js-states state-tags" name="warehouses">
                        <option value="">Choose Warehouse</option>
                        @foreach($warehouses as $key => $warehouse)
                        <option value="{{$warehouse->id}}" @if($key == 0) selected @endif>{{@$warehouse->warehouse_title}}</option>
                        @endforeach
                    </select>
                  </div>
                </div>

                <div class="col">
                  <div class="form-group">
                    <label class="text-nowrap stock-lable">Choose @if(!array_key_exists('supplier', $global_terminologies)) Supplier @else {{$global_terminologies['supplier']}} @endif</label>
                    <select class="form-control selecting-suppliers js-states state-tags" name="suppliers">
                        <option value="">Choose @if(!array_key_exists('supplier', $global_terminologies)) Supplier @else {{$global_terminologies['supplier']}} @endif</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{$supplier->id}}">{{@$supplier->reference_name}}</option>
                        @endforeach
                    </select>
                  </div>
                </div>

                <div class="col">
                  <div class="form-group">
                    <label class="text-nowrap stock-lable">Choose Primary @if(!array_key_exists('category', $global_terminologies)) Category @else {{$global_terminologies['category']}} @endif</label>
                    <select class="form-control selecting-primary-cat js-states state-tags" name="primary_category">
                        <option value="">Choose Primary @if(!array_key_exists('category', $global_terminologies)) Category @else {{$global_terminologies['category']}} @endif</option>
                        @foreach($primary_category as $p_cat)
                        <option value="{{$p_cat->id}}">{{@$p_cat->title}}</option>
                        @endforeach
                    </select>
                  </div>
                </div>

                <div class="col">
                  <div class="form-group">
                    <label class="text-nowrap stock-lable">@if(!array_key_exists('subcategory', $global_terminologies)) Sub Category @else {{$global_terminologies['subcategory']}} @endif</label>
                    <select class="form-control fill_sub_cat_div js-states state-tags" name="sub_category">
                        <option value="">Choose @if(!array_key_exists('subcategory', $global_terminologies)) Sub Category @else {{$global_terminologies['subcategory']}} @endif</option>
                    </select>
                  </div>
                </div>

                <div class="col">
                  <div class="form-group">
                    <label class="text-nowrap stock-lable">Choose @if(!array_key_exists('type', $global_terminologies)) Type @else {{$global_terminologies['type']}} @endif</label>
                    <select class="form-control product-types js-states state-tags" name="types">
                        <option value="">Choose @if(!array_key_exists('type', $global_terminologies)) Type @else {{$global_terminologies['type']}} @endif</option>
                        @foreach($types as $type)
                        <option value="{{$type->id}}">{{$type->title}}</option>
                        @endforeach
                    </select>
                  </div>
                </div>

                @if (in_array('product_type_2', $product_detail_section))
                <div class="col">
                  <div class="form-group">
                    <label class="text-nowrap stock-lable">Choose @if(!array_key_exists('product_type_2', $global_terminologies)) Type 2 @else {{$global_terminologies['product_type_2']}} @endif</label>
                    <select class="form-control product-types_2 js-states state-tags" name="types_2">
                        <option value="">Choose @if(!array_key_exists('product_type_2', $global_terminologies)) Type 2 @else {{$global_terminologies['product_type_2']}} @endif</option>
                        @foreach($types_2 as $type)
                        <option value="{{$type->id}}">{{$type->title}}</option>
                        @endforeach
                    </select>
                  </div>
                </div>
                @endif
                <div class="col">
                  <!-- <button type="button" class="btn btn-success reset_btn pull-right mt-4">Reset</button> -->
                  <label><b style="visibility: hidden;">Reset</b></label>
                  <div class="input-group-append ml-3">
                    <span class="reset_btn common-icons" title="Reset">
                      <img src="{{asset('public/icons/reset.png')}}" width="27px">
                    </span>
                  </div>
                </div>

                <div class="col pull-right">
                <button class="btn  pull-right mt-4" id="filteredProductsbtn" >Download Filtered Products</button>
                </div>

              </div>
              </form>

              <div class="form-group">
                <form action="{{route('get-all-prod-qty-excel')}}" method="POST" id="allProducts">
                  <input type="hidden" name="warehouses" id="warehouse_for_all">
                  {{csrf_field()}}
                </form>
                <button class="btn btn-info pull-right" id="alreadybtn" >Already Have File</button>
                <button class="btn btn-success pull-right d-none" id="allProductsbtn" >Download All Products</button>
              </div>

              <br>
              <div class="upload-div" style="display: none;">
                <h3>Upload File</h3>
                <label><strong>Note : </strong>Please use the downloaded file for upload only.<span class="text-danger">Also Don't Upload Empty File.</span></label>
                <form class="upload-excel-form" enctype="multipart/form-data">
                  {{csrf_field()}}
                  <label for="bulk_import_file">Choose Excel File</label>
                  <input type="file" class="form-control" name="excel" id="price_excel" accept=".xls,.xlsx" required=""><br>
                  <button class="btn btn-info quantity-upload-btn" type="submit">Upload</button>
                </form>
              </div>
              <div class="col pull-right mt-4 mb-4">
              <button class="btn bg-success pull-right mt-4" id="moveProductsToInventorybtn">Move To Inventory</button>
                <!-- <button class="btn bg-success pull-right mt-4" id="completedProductsbtn">Redownload Stock File</button> -->
              </div>
              <div class="entriesbg bg-white custompadding customborder mt-5">
              <h1 class="inccorect-datatable text-danger">Incomplete Records</h1>
                <div class="table-responsive">
                  <table class="table entriestable table-bordered table-incomplete-rows text-center">
                      <thead>
                        <tr>
                          <th>PF#
                            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="PF#">
                              <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                            </span>
                            <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="PF#">
                              <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                            </span>
                          </th>
                          <th>Supplier Name
                            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="supplier_name">
                              <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                            </span>
                            <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="supplier_name">
                              <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                            </span>
                          </th>
                          <th>Customer Name
                            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="customer_name">
                              <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                            </span>
                            <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="customer_name">
                              <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                            </span>
                          </th>
                          <th>Adjust1
                            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="adjace1">
                              <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                            </span>
                            <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="adjace1">
                              <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                            </span>
                          </th>
                          <th>Exp 1(dd/m/YYYY)
                            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="expiration_date1">
                              <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                            </span>
                            <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="expiration_date1">
                              <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                            </span>
                          </th>
                          <th>Adjust2
                            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="adjace2">
                              <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                            </span>
                            <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="adjace2">
                              <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                            </span>
                          </th>
                          <th>Exp 2(dd/m/YYYY)
                            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="expiration_date2">
                              <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                            </span>
                            <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="expiration_date2">
                              <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                            </span>
                          </th>
                          <th>Adjust3
                            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="Adjace3">
                              <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                            </span>
                            <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="Adjace3">
                              <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                            </span>
                          </th>
                          <th>Exp 3(dd/m/YYYY)
                            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="expiration_date3">
                              <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                            </span>
                            <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="expiration_date3">
                              <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                            </span>
                          </th>
                        </tr>
                      </thead>
                  </table>
                </div>
              </div>
              
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
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
<div id="user-id" class="d-none" data-user-id="{{ auth()->user()->id }}"></div>

<div class="modal" id="bulk_upload_Modal" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
        <h3 style="text-align:center;">Please wait</h3>
        <p style="text-align:center;"><img src="{{ asset('public/uploads/gif/waiting.gif') }}"></p>
        <div class="alert alert-primary export-alert"  role="alert">
            <i class="  fa fa-spinner fa-spin"></i>
            <b> File is being Uploading! Please wait.. </b>
          </div>
      </div>
    </div>
  </div>
</div>

{{-- Content End Here  --}}

@endsection

@section('javascript')

<script type="text/javascript">
$( document ).ready(function() {
  var userId = $('#user-id').data('user-id');
  var table2 = $('.table-incomplete-rows').DataTable({
      "sPaginationType": "listbox",
      processing: false,
      "language": {
        processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      ordering: false,
      searching:true,
      serverSide: true,
      scrollX: true,
      scrollY : '90vh',
      dom: 'Blfrtip',
      scrollCollapse: true,
   
      buttons: [
        {
            extend: 'colvis',
            columns: ':not(.noVis)',

        }
      ],
      "lengthMenu": [100,200,300,400],
      ajax:
      {
        beforeSend: function(){
          $('#loader_modal').modal('show');
           
        },
        url: "{!! route('get-temp-stock-adjustment-data') !!}",
        data: function(data) { data.user_id = userId
              } ,
      },
      columns: [
        { data: 'PF#', name: 'PF#' },
        { data: 'supplier_name', name: 'supplier_name' },
        { data: 'customer_name', name: 'customer_name' },
        { data: 'adjace1', name: 'adjace1' },
        { data: 'expiration_date1', name: 'expiration_date1' },
        { data: 'adjace2', name: 'adjace2' },
        { data: 'expiration_date2', name: 'expiration_date2' },
        { data: 'adjace3', name: 'adjace3' },
        { data: 'expiration_date3', name: 'expiration_date3' },
        // Add more columns as needed
    ],
      initComplete: function () {
      $(".stock-supplier-name").select2();
      $(".stock-customer-name").select2();
      },
      drawCallback: function(){
        $(".stock-supplier-name").select2();
        $(".stock-customer-name").select2();
        $('#loader_modal').modal('hide');
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
      },
  });
  //update customer and supplier name 
  $(document).on('change', '.stock-supplier-name', function() {
    let rowId = $(this).data('row-id');
    var supplierName = $(this).val();
    var type = "supplier";
    changeCustomerOrSupplierName(rowId, supplierName, type);  
  });
  $(document).on('change', '.stock-customer-name', function() {
    let rowId = $(this).data('row-id');
    var customerName = $(this).val();
    var type = "customer";
    changeCustomerOrSupplierName(rowId, customerName, type);  
  });
  
 });
 function changeCustomerOrSupplierName(rowId, selected_name, type){
  $.ajax({
      method:"get",
      data:{ id:rowId,selected_name:selected_name, type:type },
      url:"{{ route('update-cusomer-or-supplier-name') }}",
      beforeSend:function(){
      
      },
      success:function(data){
        if(data.success == true)
        {
        toastr.success('Success!', data.successMsg ,{"positionClass": "toast-bottom-right"});
        $('.table-incomplete-rows').DataTable().ajax.reload();
        return;
        }
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
   });
 }
$(function(e){

  $(".state-tags").select2();
//   $(document).on('click','.quantity-upload-btn',function(){
//     if($('.quantity-upload-btn').val() !== '') {
//       $('#loader_modal').modal({
//       backdrop: 'static',
//       keyboard: false
//     });
//     $("#loader_modal").modal('show');
//     }

//   });

  $('#allProductsbtn').on('click',function (e) {
      $('.upload-div').show(300);
      e.preventDefault();
      var w_id = $('select[name=warehouses]').val();
      $('#warehouse_for_all').val(w_id);
      $('#allProducts').submit();
    });
    $(document).on('click', '.closeErrorDiv', function (){
      $('.joberrormsgDiv').addClass('d-none');
    });
  $('#alreadybtn').on('click',function(){
    $('.upload-div').show(300);
  });

  $('#filteredProductsbtn').on('click',function(e){
    e.preventDefault();
    {{-- var supplier_id = $('.selecting-suppliers').val();
    var primary_category = $('.selecting-primary-cat').val();
    var fill_sub_cat_div = $('.fill_sub_cat_div').val();
    var product_types = $('.product-types').val(); --}}

      $('.upload-div').show(300);

        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
        });
        $.ajax({
          url:"{{ route('get-filtered-stock-prod-excel') }}",
          method:'post',
          data:$('#filteredProducts').serialize(),
          beforeSend:function()
          {
            $('#filteredProductsbtn').html('Please Wait');
            $('#filteredProductsbtn').prop('disabled',true);
          },
          success:function(data)
          {
              if(data.status==1)
              {
                $('.export-alert-success').addClass('d-none');
                $('#filteredProductsbtn').html('<i class="fa fa-spinner fa-spin"></i> Downloading! Please Wait...');
                $('#filteredProductsbtn').prop('disabled',true);
                console.log(data.status);
                recursiveCallForStatus(1);
              }
              else if(data.status==2)
              {
                  $('.export-alert-success').addClass('d-none');
                  $('#filteredProductsbtn').html('<i class="fa fa-spinner fa-spin"></i> '+data.msg);
                  $('#filteredProductsbtn').prop('disabled',true);
                  console.log(data.status);
                  recursiveCallForStatus(2);
              }


          },
          error:function()
          {
              $('#filteredProductsbtn').html('Download Filtered Products');
              $('#filteredProductsbtn').prop('disabled',false);
          }
        });

    // if(supplier_id != '' || primary_category != '' || fill_sub_cat_div != ''){
    //   $('#filteredProducts').submit();
    //   $('.upload-div').show(300);
    // }
    // else{
    //   swal('Please Select a Supplier or a Product Category for Filtering Products');
    //   e.preventDefault();
    //   return false;
    // }
  });
//download and upload completed products 
$('#completedProductsbtn').on('click',function(e){
    e.preventDefault();
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
        });
        $.ajax({
          url:"{{ route('get-completed-stock-prod-excel') }}",
          method:'post',
          beforeSend:function()
          {
            $('#completedProductsbtn').html('Please Wait');
            $('#completedProductsbtn').prop('disabled',true);
          },
          success:function(data)
          {
              if(data.status==1)
              {
                $('.export-alert-success').addClass('d-none');
                $('#completedProductsbtn').html('<i class="fa fa-spinner fa-spin"></i> Downloading! Please Wait...');
                $('#completedProductsbtn').prop('disabled',true);
                console.log(data.status);
                recursiveCallForCompletedProdStatus(1);
              }
              else if(data.status==2)
              {
                  $('.export-alert-success').addClass('d-none');
                  $('#completedProductsbtn').html('<i class="fa fa-spinner fa-spin"></i> '+data.msg);
                  $('#completedProductsbtn').prop('disabled',true);
                  console.log(data.status);
                  recursiveCallForCompletedProdStatus(2);
              }
          },
          error:function()
          {
              $('#completedProductsbtn').html('Redownload Stock File');
              $('#completedProductsbtn').prop('disabled',false);
          }
        });
  });
  function recursiveCallForCompletedProdStatus(type)
  {
    console.log('Type '+type);
    $.ajax({
            method:"get",
            url:"{{route('recursive-call-for-completed-prod-status')}}",
            success:function(data){
                  if(data.status==1)
                  {
                    console.log("Status " +data.status);
                    setTimeout(
                      function(){
                        console.log("Calling Function Again");
                        recursiveCallForCompletedProdStatus(type);
                      }, 5000);
                  }
                  else if(data.status==0)
                  {
                      var user_id={{Auth::user()->id}};
                      var url='storage/app/Completed-Stock-Adjustment-'+user_id+'-'+data.file_name+'.xlsx';
                      console.log(url);
                      $('#completedProductsbtn').html('Redownload Stock File');
                      $('.export-alert-success').removeClass('d-none');
                      toastr.success('Success!', 'File downloaded successfully.' ,{"positionClass": "toast-bottom-right"});
                      if(type==1)
                      {
                        console.log('Type 1 allowed');

                        // window.location = 'storage/app/Stock-Adjustment-'+user_id+'-'+data.file_name+'.xlsx';
                        var file = 'Completed-Stock-Adjustment-'+user_id+'-'+data.file_name+'.xlsx';


                        window.location = "{{ url('get-download-xslx') }}"+'/'+file;
                      }
                      $('#completedProductsbtn ').prop('disabled',false);
                      console.log('Export Done');
                  }
                  else if(data.status==2)
                  {
                      $('#completedProductsbtn ').html('Redownload Stock File');
                      $('#completedProductsbtn').prop('disabled',false);
                      console.log(data.exception);
                      toastr.error('Error!', 'Something went wrong. Please try again later. If the issue persists, please contact support.' ,{"positionClass": "toast-bottom-right"});

                  }
                  // else if(data.status==3)
                  // {
                  //     $('#completedProductsbtn ').html('Download & Upload Completed Products');
                  //     $('#completedProductsbtn ').prop('disabled',false);
                  // }
              }
          });
  }
  function recursiveCallForStatus(type)
  {
    console.log('Type '+type);
    $.ajax({
            method:"get",
            url:"{{route('recursive-call-for-status')}}",
            success:function(data){
                  if(data.status==1)
                  {
                    console.log("Status " +data.status);
                    setTimeout(
                      function(){
                        console.log("Calling Function Again");
                        recursiveCallForStatus(type);
                      }, 5000);
                  }
                  else if(data.status==0)
                  {
                      var user_id={{Auth::user()->id}};
                      var url='storage/app/Stock-Adjustment-'+user_id+'-'+data.file_name+'.xlsx';
                      console.log(url);
                      $('#filteredProductsbtn').html('Download Filtered Products');
                      $('.export-alert-success').removeClass('d-none');
                      toastr.success('Success!', 'File downloaded successfully.' ,{"positionClass": "toast-bottom-right"});
                      if(type==1)
                      {
                        console.log('Type 1 allowed');

                        // window.location = 'storage/app/Stock-Adjustment-'+user_id+'-'+data.file_name+'.xlsx';
                        var file = 'Stock-Adjustment-'+user_id+'-'+data.file_name+'.xlsx';


                        window.location = "{{ url('get-download-xslx') }}"+'/'+file;
                      }
                      $('#filteredProductsbtn').prop('disabled',false);
                      console.log('Export Done');
                  }
                  else if(data.status==2)
                  {
                      $('#filteredProductsbtn').html('Download Filtered Products');
                      $('#filteredProductsbtn').prop('disabled',false);
                      console.log(data.exception);
                      toastr.error('Error!', 'Something went wrong. Please try again later. If the issue persists, please contact support.' ,{"positionClass": "toast-bottom-right"});

                  }
                  else if(data.status==3)
                  {
                      $('#filteredProductsbtn').html('Download Filtered Products');
                      $('#filteredProductsbtn').prop('disabled',false);
                  }
              }
          });
  }
  $(document).on('click','.reset_btn', function(){
    $('.selecting-suppliers').val("");
    $('.selecting-primary-cat').val("");
    $('.fill_sub_cat_div').val("");
    $('.product-types').val('');
    $('.product-types_2').val('');
     $(".state-tags").select2("", "");
  });


      $(document).ready(function(){
      $.ajax({
        method:"get",
        url:"{{route('check-status-for-first-time-stock-adjustments')}}",
        success:function(data)
        {
          if(data.status==0 || data.status==2)
          {

          }
          else
          {
                $('#filteredProductsbtn').html('<i class="fa fa-spinner fa-spin"></i> Downloading! Please Wait...');
                $('#filteredProductsbtn').prop('disabled',true);
                console.log(data.status);
                recursiveCallForStatus(1);
          }
        }
      });
    });

  $(document).on('change',".selecting-primary-cat",function(){
      var category_id=$(this).val();
      // var store_sb_cat =$(this);
      $.ajax({

          url:"{{route('filter-sub-category')}}",
          method:"get",
          dataType:"json",
          data:{category_id:category_id},
          beforeSend:function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $("#loader_modal").modal('show');
          },
          success:function(data){
            $("#loader_modal").modal('hide');
              var html_string = '';
                html_string+="<option value=''>Select a Sub Category</option>";
              for(var i=0;i<data.length;i++){
                html_string+="<option value='"+data[i]['id']+"'>"+data[i]['title']+"</option>";
              }
              // $("#state_div").remove();
              // store_sb_cat.after($("<div></div>").text(html_string));
              $(".fill_sub_cat_div").empty();
              $(".fill_sub_cat_div").append(html_string);

          },
          error: function(request, status, error){
            $("#loader_modal").modal('hide');
            alert('Error');
          }

      });
  });

  $(document).on('click', '.closeErrorDiv', function (){
    $('.errormsgDiv').hide();
    $('.error_div').addClass('d-none');
  });

  @if(Session::has('successmsg'))
      swal( "{{ Session::get('successmsg') }}");
      @php
       Session()->forget('successmsg');
      @endphp
  @endif
  @if(Session::has('msg'))
      swal( "{{ Session::get('msg') }}");
      @php
       Session()->forget('msg');
      @endphp
  @endif
  @if(Session::has('errormsg'))
      $('.error_div').removeClass('d-none');
      var msg = "{{Session::get('errormsg') }}";
       var data = msg.split("&amp;");
      var html = '<ol>';
      for(var i =0; i< data.length; i++){
        if(i != data.length-1){
          html += '<li>'+data[i]+'</li>';
        }
      }
      html += '</ol>';
      $('.error_msg_div').append(html);
      swal("Some of them have Error");
      @php
       Session()->forget('errormsg');
      @endphp
  @endif

  if($('.selecting-primary-cat').val() != '')
  {
      var category_id=$('.selecting-primary-cat').val();
      // var store_sb_cat =$(this);
      $.ajax({

          url:"{{route('filter-sub-category')}}",
          method:"get",
          dataType:"json",
          data:{category_id:category_id},
          beforeSend:function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $("#loader_modal").modal('show');
          },
          success:function(data){
            $("#loader_modal").modal('hide');
              var html_string = '';
                html_string+="<option value=''>Select a Sub Category</option>";
              for(var i=0;i<data.length;i++){
                html_string+="<option value='"+data[i]['id']+"'>"+data[i]['title']+"</option>";
              }
              // $("#state_div").remove();
              // store_sb_cat.after($("<div></div>").text(html_string));
              $(".fill_sub_cat_div").empty();
              $(".fill_sub_cat_div").append(html_string);
              var getsession = window.sessionStorage.getItem('form-controlfill_sub_cat_divjs-statesstate-tags');
              if(getsession != null)
              {
                $('.fill_sub_cat_div').val(getsession);
              }


          },
          error: function(request, status, error){
            $("#loader_modal").modal('hide');
            alert('Error');
          }

      });
  }
$(document).on('submit', '.upload-excel-form', function(e){
        e.preventDefault();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.ajax({

            method:"post",
            url:"{{route('bulk-upload-prod-qty')}}",
            data: new FormData(this),
            contentType: false,
            cache: false,
            processData:false,
            beforeSend:function(){
                $('#bulk_upload_Modal').modal({
                    backdrop: 'static',
                    keyboard: false
                });
                $("#bulk_upload_Modal").modal('show');
                // $('.inccorect-datatable').addClass('d-none');
            },
            success:function(data)
            {
                if(data.status==0 || data.status==2)
                {
                  $('.table-incomplete-rows').DataTable().ajax.reload();
                }
                else
                {
                    console.log(data.status);
                    $('.table-incomplete-rows').DataTable().ajax.reload();
                    recursiveCallForImportStatus();
                }
            },
            error: function(xhr, status, error) {
            var response = JSON.parse(xhr.responseText);
            var errorMessage = response.message;
            var errorDetails = response.errors;
            var errorText = '';

            // Format the error details
            for (var key in errorDetails) {
                if (errorDetails.hasOwnProperty(key)) {
                    errorText += key + ': ' + errorDetails[key].join(', ') + '<br>';
                }
            }

            // Display swal with error message and details
            swal("The given data is invalid, The excel must be a file of type: xlsx.", "", "error");

            $('#bulk_upload_Modal').modal('hide');

           }
            });
    });
    $(document).on('click', '#moveProductsToInventorybtn', function(e){
        e.preventDefault();
        $.ajax({

            method:"get",
            url:"{{route('bulk-completed-prod-move-to-inventory')}}",
            contentType: false,
            cache: false,
            processData:false,
            beforeSend:function(){
            $('#moveProductsToInventorybtn').html('Please Wait');
            $('#moveProductsToInventorybtn').prop('disabled',true);
            },
            success:function(data)
            {
                if(data.status==0 || data.status==2)
                {
                  $('.table-incomplete-rows').DataTable().ajax.reload();
                  $('#moveProductsToInventorybtn').html('Move To Inventory');
                  $('#moveProductsToInventorybtn').prop('disabled',false);
                }
                else
                {
                    console.log(data.status);
                    recursiveCallForImportStatus();
                    $('#moveProductsToInventorybtn').html('Move To Inventory');
                   $('#moveProductsToInventorybtn').prop('disabled',false);
                   $('.table-incomplete-rows').DataTable().ajax.reload();
                  
                    // $('.table-incomplete-rows').DataTable().ajax.reload();
                    // recursiveCallForImportStatus();
                }
            },
            error: function(xhr, status, error) {
            var response = JSON.parse(xhr.responseText);
            var errorMessage = response.message;
            var errorDetails = response.errors;
            var errorText = '';

            // Format the error details
            for (var key in errorDetails) {
                if (errorDetails.hasOwnProperty(key)) {
                    errorText += key + ': ' + errorDetails[key].join(', ') + '<br>';
                }
            }

            // Display swal with error message and details
            swal("The given data is invalid.", "", "error");

            }
         });
    });

    function recursiveCallForImportStatus()
    {
        $.ajax({
            method:"get",
            url:"{{route('recursive-call-for-import-status')}}",
            success:function(data){
                if(data.status==1)
                {
                    console.log("Status " +data.status);
                    setTimeout(
                        function(){
                        console.log("Calling Function Again");
                        recursiveCallForImportStatus();
                        }, 5000);
                }
                else if(data.status==0)
                {
                    // toastr.success('Success!', 'Stock Adjust Successfully.' ,{"positionClass": "toast-bottom-right"});
                    swal("Stock Adjusted Successfully", "", "success");
                    $('#bulk_upload_Modal').modal('hide');
                    $('.table-incomplete-rows').DataTable().ajax.reload();

                }
                else if(data.status==2)
                {
                    console.log(data.exception);
                    $('#bulk_upload_Modal').modal('hide');

                    // toastr.error('Error!', 'Something went wrong. Please try again later. If the issue persists, please contact support.' ,{"positionClass": "toast-bottom-right"});
                    swal("Something went wrong. Please try again later. If the issue persists, please contact support", "", "error");
                }
                else if(data.status == 3)
                {
                    // console.log(data.exception);
                    $('#bulk_upload_Modal').modal('hide');
                    // toastr.error('Error!', 'Something went wrong. Please try again later. If the issue persists, please contact support.' ,{"positionClass": "toast-bottom-right"});
                    swal("Some products have incomplete data please check the Incomplete Records Table", "", "error");
                    $('.table-incomplete-rows').DataTable().ajax.reload();
                    // $('.inccorect-datatable').html("Incomplete Records");
                    // $('.inccorect-datatable').removeClass('d-none');

                    // $('.joberrormsgDiv').removeClass('d-none');
                }
            }
        });
    }
});
</script>
@endsection
