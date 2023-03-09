@extends('users.layouts.layout')

@section('title','Suppliers Management | Supply Chain')

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
          <li class="breadcrumb-item active">Add Bulk Suppliers</li>
      </ol>
  </div>
</div>

<div class="row mb-3">
  <div class="col-md-8 title-col">
    <h3 class="maintitle text-uppercase fontbold">Add Bulk Suppliers</h3>
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
              <a class="nav-link cut-tab active bulk-upload" data-toggle="tab" href="#tab1">Add Bulk Suppliers</a>
            </li>
            <li class="nav-item ">
              <a class="nav-link cut-tab bulk-upload" data-toggle="tab" href="#tab2">Add Bulk PO's</a>
            </li>
          </ul>

          <div class="tab-content mt-3">
            <div class="tab-pane active" id="tab1">
              <button class="btn btn-info pull-right" id="alreadybtn" >Already Have File</button>
              <a href="{{asset('public/site/assets/purchasing/suppliers_excel/Bulk_Suppliers.xlsx')}}" download><span class="btn btn-success pull-right  mr-1" id="examplefilebtn">Download Example File</span></a>

              <br>
              <div class="upload-div" style="display: none;">
                <h3>Upload File</h3>
                <label><strong>Note : </strong>Please use the downloaded file for upload only.<span class="text-danger">Also Don't Upload Empty File.</span></label>
                <form action="{{url('bulk-upload-suppliers')}}" class="upload-excel-form" method="post" enctype="multipart/form-data">
                  {{csrf_field()}}
                  <label for="bulk_import_file">Choose Excel File</label>
                  <input type="file" class="form-control" name="excel" id="price_excel" accept=".xls,.xlsx" required="true"><br>
                  <button class="btn btn-info price-upload-btn" type="submit">Upload</button>
                </form>
              </div>

            </div>

            <div class="tab-pane" id="tab2">
              <button class="btn btn-info pull-right" id="alreadybtnPO" >Already Have File</button>
              <a href="{{asset('public/site/assets/purchasing/suppliers_excel/Bulk_POs.xlsx')}}" download><span class="btn btn-success pull-right  mr-1" id="examplefilebtnPo">Download Example File</span></a>

              <br>
              <div class="upload-div-PO" style="display: none;">
                <h3>Upload File</h3>
                <label><strong>Note : </strong>Please use the downloaded file for upload only.<span class="text-danger">Also Don't Upload Empty File.</span></label>
                <form class="upload-excel-form bulk-upload-pos-form" method="post" enctype="multipart/form-data">
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
$(function(e){
  $(document).on('click','.price-upload-btn',function(){
    if($('#price_excel').val() == '') {
      $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
      $("#loader_modal").modal('hide');
    } else {
      $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
      $("#loader_modal").modal('show');
    }
  });

  $('#allProductsbtn').on('click',function (e) {
      $('.upload-div').show(300);
      e.preventDefault();
      $('#allProducts').submit();
    });

  $('#alreadybtn').on('click',function(){
    $('.upload-div').show(300);
  });

  $('#alreadybtnPO').on('click',function(){
    $('.upload-div-PO').show(300);
  });

  $('#filteredProductsbtn').on('click',function(e){
    var supplier_id = $('.selecting-suppliers').val();
    var primary_category = $('.selecting-primary-cat').val();
    if(supplier_id != '' || primary_category != ''){
      $('#filteredProducts').submit();
      $('.upload-div').show(300);
    }
    else{
      swal('Please Select a Supplier or a Product Category for Filtering Products');
      e.preventDefault();
      return false;
    }
  });

  $(document).on('click', '.closeErrorDiv', function (){
    $('.errormsgDiv').hide();
  });

  @if(Session::has('errMsg'))
      swal( "{{ Session::get('errMsg') }}");
      @php
       Session()->forget('errMsg');
      @endphp
  @endif

  @if(Session::has('successmsg'))
      swal( "{{ Session::get('successmsg') }}");
      @php
       Session()->forget('successmsg');
      @endphp
  @endif



  $('.bulk-upload-pos-form').submit(function (e) {
    e.preventDefault();
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method:'post',
      url: '{{route("bulk-upload-pos")}}',
      data: new FormData(this),
      contentType: false,
      cache: false,
      processData:false,
      success: function (data) {
        if(data.status==1)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').removeClass('d-none');
          $('.export_btn').attr('title','EXPORT is being Prepared');
          $('.export_btn').prop('disabled',true);
          console.log("Calling Function from first part");
          checkStatusForPOs();
        }
        else if(data.status==2)
        {
          $('.export-alert-another-user').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export_btn').prop('disabled',true);
          $('.export_btn').attr('title','EXPORT is being Prepared');
          checkStatusForPOs();
        }
      }
    });
  });

  function checkStatusForPOs()
  {
    $.ajax({
      method:"get",
      url:"{{route('recursive-import-status-bulk-pos')}}",
      success:function(data){
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
          $('.export-alert-success').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-alert-another-user').addClass('d-none');
          if (data.error_msgs != null) {
            $('#msgs_alerts').html(data.error_msgs);
            $('.errormsgDivBulk').removeClass('d-none');
            toastr.success('Success!', 'Data Uploaded Successfully. Some rows are incomplete. Please review and upload again.' ,{"positionClass": "toast-bottom-right"});
          }
          else{
           // $('.errormsgDivBulk').addClass('d-none');
           window.location.href = '{{route("received-into-stock")}}';
          }
        }
        else if(data.status==2)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-alert-another-user').addClass('d-none');
          $('#msgs_alerts').html(data.error_msgs);
          $('.errormsgDivBulk').removeClass('d-none');
        }
      }
    });
  }

  $('.closeErrorDiv').click(function () {
    $('.errormsgDivBulk').addClass('d-none');
  })

  $( document ).ready(function() {

    $.ajax({
      method:"get",
      url:"{{route('check-status-for-first-time-bulk-pos')}}",
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
});
</script>
@endsection
