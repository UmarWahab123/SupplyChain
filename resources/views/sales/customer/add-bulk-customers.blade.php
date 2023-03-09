@extends('sales.layouts.layout')
@section('title','Customer Management | Supply Chain')

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
          <li class="breadcrumb-item active">Temp Bulk Customers</li>
      </ol>
  </div>
</div>

<div class="row mb-3">
  <div class="col-md-8 title-col">
    <h3 class="maintitle text-uppercase fontbold">Temp Bulk Customers</h3>
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


<div class="row errormsgDivBulk d-none">
    <div class="container" style="max-width: 50% !important; min-width: 50% !important">
      <div class="alert alert-danger alert-dismissible">
        <a href="javascript:void(0)" class="closeErrorDiv">&times;</a>
        <div id="msgs_alerts"></div>
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
              <a class="nav-link cut-tab active" data-toggle="tab" href="#tab1">Add Bulk Customers</a>
            </li>
            <li class="nav-item ">
              <a class="nav-link cut-tab bulk-upload" data-toggle="tab" href="#tab2">Add Bulk Customer Orders</a>
            </li>
          </ul>

          <div class="tab-content mt-3">
            <div class="tab-pane active" id="tab1">
              <button class="btn btn-info pull-right" id="alreadybtn" >Already Have File</button>
              <a href="{{asset('public/site/assets/sales/customers_excel/customer_upload_example.xlsx')}}" download><span class="btn btn-success pull-right  mr-1" id="examplefilebtn">Download Example File</span></a>

              <br>
              <div class="upload-div" style="display: none;">
                <h3>Upload File</h3>
                <label><strong>Note : </strong>Please use the downloaded file for upload only.<span class="text-danger">Also Don't Upload Empty File.</span></label>
                <form action="{{route('bulk-upload-customers')}}" class="upload-excel-form" method="post" enctype="multipart/form-data">
                  {{csrf_field()}}
                  <label for="bulk_import_file">Choose Excel File</label>
                  <input type="file" class="form-control" name="customer_excel" id="customer_excel" accept=".xls,.xlsx" required=""><br>
                  <button class="btn btn-info customer-upload-btn">Upload</button>
                </form>
              </div>

            </div>



            <div class="tab-pane" id="tab2">
              <button class="btn btn-info pull-right" id="alreadybtnPO" >Already Have File</button>
              <a href="{{asset('public/site/assets/sales/customers_excel/Customer_bulk_orders.xlsx')}}" download><span class="btn btn-success pull-right  mr-1" id="examplefilebtnPo">Download Example File</span></a>

              <br>
              <div class="upload-div-PO" style="display: none;">
                <h3>Upload File</h3>
                <label><strong>Note : </strong>Please use the downloaded file for upload only.<span class="text-danger">Also Don't Upload Empty File.</span></label>
                <form class="bulk-upload-pos-form" method="post" enctype="multipart/form-data">
                  {{csrf_field()}}
                  <label for="bulk_import_file">Choose Excel File</label>
                  <input type="file" class="form-control" name="excel" id="price_excel" accept=".xls,.xlsx" required=""><br>
                  <button class="btn btn-info po-upload-btn" type="submit">Upload</button>
                </form>

                <div class="alert alert-primary export-alert d-none mt-2"  role="alert">
                  <i class="  fa fa-spinner fa-spin"></i>
                  <b> File is Uploading! Please wait.. </b>
                </div>
                <div class="alert alert-success export-alert-success d-none mt-2"  role="alert">
                  <i class=" fa fa-check "></i>
                  <b>File Uploaded Successfully.
                  </b>
                </div>
                <div class="alert alert-primary export-alert-another-user d-none mt-2"  role="alert">
                  <i class="  fa fa-spinner fa-spin"></i>
                  <b> File is uploaded by another user! Please wait.. </b>
                </div>
              </div>




          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{--Error msgs div--}}
<div class="row errormsgDiv mt-2" style="display: none;">
  <div class="container" style="max-width: 100% !important; min-width: 100% !important">
    <div class="alert alert-danger alert-dismissible">
      <a href="javascript:void(0)" class="closeErrorDiv">&times;</a>
      <span id="errormsg"></span>
    </div>
  </div>
</div>

<div class="row entriestable-row mt-2 w-100">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
      <a style="cursor: pointer;" class="pull-right" href="JavaScript:void(0);" id="move_customers_to_inventory" title="Move to inventory">
        <img src="{{asset('public/img/Product-Bulk-Upload1.png')}}" style="width: 40px; height: 40px; margin-right: 10px; margin-bottom: 10px;" title="Move to inventory" class="image">
      </a>
      <div class="delete-selected-item catalogue-btn-group d-none">
        <a href="javascript:void(0);" class="btn selected-item-btn btn-sm deleteBtnImg delete-btn" title="Delete Selected Items"><i class="fa fa-trash"></i></a>
      </div>


      <div class="table-responsive">
        <table class="table entriestable table-bordered table-temp_customers text-center">
          <thead>
            <tr>
              <th class="noVis">
                <div class="custom-control custom-checkbox d-inline-block">
                  <input type="checkbox" class="custom-control-input check-all" name="check_all" id="check-all">
                  <label class="custom-control-label" for="check-all"></label>
                </div>
              </th>
              <!-- <th>Reference #</th> -->
              <th>Reference Name </th>
              <th>Primary Sale</th>
              <th>Secondary Sale</th>
              <th> {{$global_terminologies['company_name']}} </th>
              <th>Classification</th>
              <th>Credit Terms</th>
              <th>Payment Method</th>
              <th>Address Reference Name</th>
              <th>Phone No.</th>
              <th>Moblie No.</th>
              <th>Address</th>
              <th>Tax ID</th>
              <th>Email</th>
              <th>Fax</th>
              <th>District</th>
              <th>City</th>
              <th>{{$global_terminologies['zip_code']}} </th>
              <th>Name</th>
              <th>Sur Name</th>
              <th>Email</th>
              <th>Telephone</th>
              <th>Position</th>
              <th>Status</th>
            </tr>
          </thead>

        </table>
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


{{-- Content End Here  --}}

@endsection

@section('javascript')

<script type="text/javascript">


$('.bulk-upload-pos-form').submit(function (e) {
    e.preventDefault();
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method:'post',
      url: '{{route("customer-bulk-upload-pos")}}',
      data: new FormData(this),
      contentType: false,
      cache: false,
      processData:false,
      success: function (data) {
        if(data.status==1)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').removeClass('d-none');
          $('.export_btn').attr('title','Import is being Prepared');
          $('.export_btn').prop('disabled',true);
          checkStatusForPOs();
        }
        else if(data.status==2)
        {
          $('.export-alert-another-user').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export_btn').prop('disabled',true);
          $('.export_btn').attr('title',data.error_msgs);

          checkStatusForPOs();
        }
      }
    });
  });

  $('.closeErrorDiv').click(function () {
    $('.errormsgDivBulk').addClass('d-none');
  })

  function checkStatusForPOs()
  {
    $.ajax({
      method:"get",
      url:"{{route('customer-recursive-import-status-bulk-pos')}}",
      success:function(data){
        console.log(data);
        if(data.status==1)
        {
          console.log("Status " +data.status);
          setTimeout(
            function(){
              console.log("Calling Function Again");
              checkStatusForPOs();
            }, 5000);
        }
        else if(data.status==0)
        {
            console.log(data);
          $('.export-alert-success').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-alert-another-user').addClass('d-none');
          if (data.error_msgs != null) {
            $('#msgs_alerts').html(data.error_msgs);
            $('.errormsgDivBulk').removeClass('d-none');
            toastr.warning('Success!', 'Some rows are incomplete. Please review and upload again.' ,{"positionClass": "toast-bottom-right"});
          }
          else{
           window.location.href = '{{route("invoices")}}';
           toastr.success('Success!', 'Data Uploaded Successfully.' ,{"positionClass": "toast-bottom-right"});
          }
        }

        else if(data.status==2)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-alert-another-user').addClass('d-none');
          toastr.warning('Warning !', data.error_msgs ,{"positionClass": "toast-bottom-right"});
        }
      }
    });
  }


  $( document ).ready(function() {

$.ajax({
  method:"get",
  url:"{{route('customer-check-status-for-first-time-bulk-pos')}}",
  success:function(data)
  {
    if(data.status==0 || data.status==2)
    {

    }
    else
    {
      $('.export-alert').removeClass('d-none');
      $('.export-alert-success').addClass('d-none');
      $('.export-alert-another-user').addClass('d-none');
      checkStatusForPOs();
    }
  }
});
});





  $('#alreadybtnPO').on('click',function(){
    $('.upload-div-PO').show(300);
  });

  $(document).on('click', '.closeErrorDiv', function (){
    $('.errormsgDiv').hide();
  });

  $(document).ready(function(){


    var table2 = $('.table-temp_customers').DataTable({
      "sPaginationType": "listbox",
     processing: false,
     "language": {
      processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      ordering: false,
      serverSide: true,
        // fixedHeader: true,
        "lengthMenu": [100,200,300,400],

        scrollX:true,
        ajax:
        {
            beforeSend: function(){
            $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
              });
            $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
            $("#loader_modal").modal('show');
          },
          url: "{!! route('get-temp-customers') !!}",
        },
        columns: [
        { data: 'checkbox', name: 'checkbox' },
        // { data: 'reference_number', name: 'reference_number' },
        { data: 'reference_name', name: 'reference_name' },
        { data: 'sales_person', name: 'sales_person' },
        { data: 'secondary_sale', name: 'secondary_sale' },
        { data: 'company_name', name: 'company_name' },
        { data: 'classification', name: 'classification' },
        { data: 'credit_term', name: 'credit_term' },
        { data: 'payment_method', name: 'payment_method' },
        { data: 'address_reference_name', name: 'address_reference_name' },
        { data: 'phone_no', name: 'phone_no' },
        { data: 'cell_no', name: 'cell_no' },
        { data: 'address', name: 'address' },
        { data: 'tax_id', name: 'tax_id' },
        { data: 'email', name: 'email' },
        { data: 'fax', name: 'fax' },
        { data: 'state', name: 'state' },
        { data: 'city', name: 'city' },
        { data: 'zip', name: 'zip' },
        { data: 'contact_name', name: 'contact_name' },
        { data: 'contact_sur_name', name: 'contact_sur_name' },
        { data: 'contact_email', name: 'reference_number' },
        { data: 'contact_tel', name: 'contact_tel' },
        { data: 'contact_position', name: 'contact_position' },
        { data: 'status', name: 'status' },
        ],
        initComplete: function () {
          $('.payment_method').selectpicker();

          // Enable THEAD scroll bars
          $('.dataTables_scrollHead').css('overflow', 'auto');

          // Sync THEAD scrolling with TBODY
          $('.dataTables_scrollHead').on('scroll', function () {
            $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
          });
        },
        "drawCallback": function(settings) {
          $('.payment_method').selectpicker();
          $("#loader_modal").modal('hide');
        }
      });

    $('.dataTables_filter input').unbind();
    $('.dataTables_filter input').bind('keyup', function(e) {
      if(e.keyCode == 13) {
  // alert();
  table2.search($(this).val()).draw();
}
});

    $(document).on('click', '.check-all', function () {

      if(this.checked == true){
        $('.check').prop('checked', true);
        $('.check').parents('tr').addClass('selected');
        var cb_length = $( ".check:checked" ).length;
        if(cb_length > 0){
          $('.delete-selected-item').removeClass('d-none');
        }
      }else{
        $('.check').prop('checked', false);
        $('.check').parents('tr').removeClass('selected');
        $('.delete-selected-item').addClass('d-none');

      }
    });

    $(document).on('click', '.check', function () {
      if(this.checked == true){
        $('.delete-selected-item').removeClass('d-none');
        $(this).parents('tr').addClass('selected');
      }else{
        var cb_length = $( ".check:checked" ).length;
        $(this).parents('tr').removeClass('selected');
        if(cb_length == 0){
         $('.delete-selected-item').addClass('d-none');
       }

     }
   });

    $(document).on("click",'.delete-btn',function(){
      var selected_customers = [];
      $("input.check:checked").each(function() {
        selected_customers.push($(this).val());
      });

      swal({
        title: "Alert!",
        text: "Are you sure you want to delete selected Temp Customers?",
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
          data:'selected_customers='+selected_customers,
          url:"{{ route('delete-temp-customers') }}",
          beforeSend:function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $("#loader_modal").modal('show');
          },
          success:function(data){
            $("#loader_modal").modal('hide');
            if(data.success == true)
            {
              toastr.success('Success!', 'Selected Temp Customers(s) deleted Successfully.' ,{"positionClass": "toast-bottom-right"});
              $('.table-temp_customers').DataTable().ajax.reload();
              $('.delete-selected-item').addClass('d-none');
              $('.check-all').prop('checked',false);
            }
            if(data.success == false)
            {
              toastr.error('Error!', data.msg ,{"positionClass": "toast-bottom-right"});
              $('.table-temp_customers').DataTable().ajax.reload();
              $('.delete-selected-item').addClass('d-none');
              $('.check-all').prop('checked',false);
            }

          },
          error: function (request, status, error) {
            $("#loader_modal").modal('hide');
            toastr.error('Error!', 'Something went wrong. Please try again later. If the issue persists, please contact support.' ,{"positionClass": "toast-bottom-right"});

          }
        });
       }
       else{
        swal("Cancelled", "", "error");
      }
    });

    });

    $(document).on("click",'#move_customers_to_inventory',function(){
      var selected_temp_customers = [];
      $("input.check:checked").each(function() {
        selected_temp_customers.push($(this).val());
      });
      if(selected_temp_customers == '')
      {
        toastr.info('info!', 'Select Customers first' ,{"positionClass": "toast-top-right"});

        return false;
      }
      swal({
        title: "Alert!",
        text: "Are you sure you want to move customers to inventory?",
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
          data:'selected_temp_customers='+selected_temp_customers,
          url:"{{ route('move-customers-to-inventory') }}",
          beforeSend:function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $("#loader_modal").modal('show');
          },
          success:function(data){
            $("#loader_modal").modal('hide');
            if(data.success == true)
            {
              toastr.success('Success!', 'Selected Complete Temp Customers(s) moved to inventory  Successfully.' ,{"positionClass": "toast-bottom-right"});
              $('.table-temp_customers').DataTable().ajax.reload();
              $('.delete-selected-item').addClass('d-none');
              $('.check-all').prop('checked',false);
            }

            if(data.customers == 'incomplete')
            {
              toastr.error('Error!', 'Selected Customers have incorrect data.',{"positionClass": "toast-bottom-right"});
              $('.table-temp_customers').DataTable().ajax.reload();
              $('.delete-selected-item').addClass('d-none');
              $('.check-all').prop('checked',false);
            }

            if(data.success == false)
            {
              if(data.errorMsg)
              {
                $('.errormsgDiv').show();
                $('#errormsg').html(data.errorMsg);
                $('.table-temp_customers').DataTable().ajax.reload();
                $('.delete-selected-item').addClass('d-none');
                $('.check-all').prop('checked',false);
              }
              else
              {
                toastr.error('Error!', 'No Cutomers in temp table' ,{"positionClass": "toast-bottom-right"});
                $('.table-temp_customers').DataTable().ajax.reload();
                $('.delete-selected-item').addClass('d-none');
                $('.check-all').prop('checked',false);
              }
            }

          },
          error: function (request, status, error) {
            $("#loader_modal").modal('hide');
            toastr.error('Error!', 'Something went wrong. Please try again later. If the issue persists, please contact support.' ,{"positionClass": "toast-bottom-right"});

          }
        });
       }
       else{
        swal("Cancelled", "", "error");
      }
    });

    });

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
        // $x.next().next('span').removeClass('d-none');
        // $x.next().next('span').addClass('active');

      }, 300);
    });

    $(document).on('keypress keyup focusout', '.fieldFocus', function(e){
      if (e.keyCode === 27 && $(this).hasClass('active')) {
        var fieldvalue = $(this).prev().data('fieldvalue');
        var thisPointer = $(this);
        thisPointer.addClass('d-none');
        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
      }

      if( (e.keyCode === 13 || e.which === 0) && $(this).hasClass('active')){

        var tcID = $(this).parents('tr').attr('id');
        if($(this).val() !== '' && $(this).hasClass('active'))
        {
          var new_value = $(this).val();
          $(this).removeClass('active');
          $(this).addClass('d-none');
          $(this).prev().removeClass('d-none');
          $(this).prev().html(new_value);
          $(this).prev().css("color", "");
          saveTempCustomerData(tcID, $(this).attr('name'), $(this).val());
        }
      }
    });

    $(document).on('change', 'select.select-common', function(){

      if($(this).val() !== '')
      {
        var tcID = $(this).parents('tr').attr('id');
        var s_payment_methods = "";

        var selected_length = $("option:selected", this).length;

        $("option:selected", this).each(function(key , value) {
          if( selected_length != key+1 )
          {
            s_payment_methods += $(this).html()+",";
          }
          else
          {
            s_payment_methods += $(this).html();
          }
        });

        var new_value = s_payment_methods;

        $(this).removeClass('active');
        $(this).addClass('d-none');
        $(this).prev().removeClass('d-none');
        $(this).prev().html(new_value);
        $(this).prev().css("color", "");
        saveTempCustomerData(tcID, $(this).attr('id'), s_payment_methods);

      }
    });

    function saveTempCustomerData(temp_customer_id,field_name,field_value){
      console.log(field_name+' '+field_value+' '+temp_customer_id);

      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method: "post",
        url: "{{ route('save-temp-customer-data') }}",
        dataType: 'json',
        data: 'temp_customer_id='+temp_customer_id+'&'+field_name+'='+field_value,
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
            $("#loader_modal").modal('hide');
            toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
            $('.table-temp_customers').DataTable().ajax.reload();
          }
        },
        error: function (request, status, error) {
          $("#loader_modal").modal('hide');
          toastr.error('Error!', 'Something went wrong. Please try again later. If the issue persists, please contact support.' ,{"positionClass": "toast-bottom-right"});

        },

      });
    }

  });
$(function(e){


  $(document).on('click','.customer-upload-btn',function(){
    var file_val = $('#customer_excel').val();
    if(file_val != '')
    {
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $("#loader_modal").modal('show');
      $('.upload-excel-form').submit();
    }

  });


  $('#alreadybtn').on('click',function(){
    $('.upload-div').show(300);
  });

  $(document).on('click', '.closeErrorDiv', function (){
    $('.errormsgDiv').hide();
  });

  @if(Session::has('successmsg'))
      swal( "{{ Session::get('successmsg') }}");
      @php
       Session()->forget('successmsg');
      @endphp
  @endif

});
</script>
@endsection
