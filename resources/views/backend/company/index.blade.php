@extends('backend.layouts.layout')

@section('title','Companies | Supply Chain')

@section('content')
<style type="text/css">
.invalid-feedback {
     font-size: 100%;
}
.disabled:disabled{
  opacity:0.5;
  cursor: not-allowed;
}

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
          <li class="breadcrumb-item active">Companies</li>
      </ol>
  </div>
</div>

<!-- cust-cat = customer-category -->
{{-- Content Start from here --}}
<div class="row align-items-center mb-3">
  <div class="col-md-10 title-col">
    <h4 class="maintitle">Companies</h4>
  </div>
  <div class="col-md-2 text-right">
    <div class="mb-0">
      <!-- <a href="#" class="btn button-st" data-toggle="modal" data-target="#addC">ADD Company</a> -->
      <a href="{{route('add-new-company')}}" class="btn button-st">ADD Company</a>
    </div>
    <div class="mb-0">
      <a href="javascript:void(0);" class="btn button-st update_warehosues_on_product_level d-none">Update Warehouses On Product Level</a>
    </div>
  </div>
</div>

{{-- <div class="row mb-0">
  <div class="col-md-12 title-col">
    <div class="d-sm-flex justify-content-between">
      <h4 class="text-uppercase fontbold">Companies</h4>
        <div class="mb-0 d-none">
        <a href="#" class="btn button-st" data-toggle="modal" data-target="#addC">ADD Company</a>
        </div>
        <div class="mb-0">
        <a href="javascript:void(0);" class="btn button-st update_warehosues_on_product_level d-none">Update Warehouses On Product Level</a>
        </div>
    </div>
  </div>
</div>
 --}}

<div class="row entriestable-row mt-2">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
    <div class="table-responsive">
      <table class="table entriestable table-bordered companies-table text-center">
        <thead>
          <tr>
            <th>Action</th>
            <th>logo</th>
            <th>Prefix</th>
            <th>Separate <br> Counter</th>
            <th>{{$global_terminologies['company_name']}}</th>
            <th>{{(array_key_exists('thai_billing_name', $global_terminologies)) ? $global_terminologies['thai_billing_name'] : 'Billing Name (THAI)'}}
            </th>
            <th>Tax ID</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Fax</th>
            <th>Address</th>
            <th>Address (THAI)</th>
            <th>District</th>
            <th>City</th>
            <th>Country</th>
            <th>ZIP</th>
          </tr>
        </thead >
      </table>
    </div>
    </div>
  </div>
</div>

</div>
<!--  Content End Here -->

<!--  cust-cat Modal Start Here -->
<div class="modal fade" id="addC">
    <div class="modal-dialog modal-lg modal-dialog-centered parcelpop">
      <div class="modal-content">
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div>
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Add Company</h3>
          <div class="mt-5">
            <form method="POST" class="add-company">
            <div class="form-row">
              <div class="form-group col-6 input-group">
                <input type="text" name="company_name" class="font-weight-bold form-control-lg form-control" placeholder="Enter Billing Name">
              </div>
              <div class="form-group col-6 input-group">
              <input type="email" name="billing_email" class="font-weight-bold form-control-lg form-control" placeholder="Enter Company Email">
            </div>
            </div>
            <div class="form-row">
              <div class="form-group col-6 input-group">
                <input type="text" name="tax_id" class="font-weight-bold form-control-lg form-control" placeholder="Enter Company Tax ID">
              </div>

              <div class="form-group col-6 input-group">
              <input type="text" name="billing_fax" class="font-weight-bold form-control-lg form-control" placeholder="Enter Company Fax">
            </div>
            </div>
            <div class="form-row">
              <div class="form-group col-6 input-group">
                <input type="text" name="billing_address" class="font-weight-bold form-control-lg form-control" placeholder="Enter Company Address">
              </div>
              <div class="form-group col-6 input-group">
              <input type="text" name="billing_zip" class="font-weight-bold form-control-lg form-control" placeholder="Enter Company ZIP">
            </div>
            </div>
            <div class="form-row">
              <div class="form-group col-6 input-group">
                  <select name="billing_country"  class="form-control selectpicker country" title="Choose Country" data-live-search="true" data-select_type="country">

                    @foreach($countries  as $result)
                     <option value="{{$result['id']}}">{{$result['name']}}</option>
                    @endforeach

                  </select>
              </div>
              <div class="form-group col-6 input-group">
                <select id="state" name="billing_state" class="form-control selectpicker" title="Choose State" data-live-search="true" data-select_type="state">
                 </select>
            </div>
            </div>
            <div class="form-row">
              <div class="form-group col-6 input-group">
                <input type="text" name="billing_city" class="font-weight-bold form-control-lg form-control" placeholder="Enter Company City">
              </div>
               <div class="form-group col-6 input-group">
                <input type="text" name="billing_phone" class="font-weight-bold form-control-lg form-control" placeholder="Enter Company Phone">
              </div>

            </div>
            <div class="form-row">
               <div class="form-group col-6 input-group">
                <input type="file" class="form-control form-control-lg" name="logo">
              </div>
            </div>

            <div class="form-submit">
              <input type="submit" value="add" class="btn btn-bg save-btn">
              <input type="reset" value="close" class="btn btn-danger close-btn">
            </div>
            </form>
         </div>
        </div>
      </div>
    </div>
  </div>
<!-- add cust-cat Modal End Here -->

{{--Edit Modal--}}
<div class="modal" id="editSupplierModal">
    <div class="modal-dialog modal-lg modal-dialog-centered parcelpop">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body text-center" id="editSupplierModalForm">

            </div>
        </div>
    </div>
</div>

<!-- Loader Modal -->
<div class="modal" id="loader_modal" role="dialog">
<div class="modal-dialog modal-sm">
  <div class="modal-content">
    <div class="modal-body">
      <h3 style="text-align:center;">Please wait</h3>
      <p style="text-align:center;"><img src="{{ asset('public/uploads/gif/waiting.gif') }}"></p>
      <div id="msg"></div>
    </div>
  </div>
</div>
</div>
@endsection

@section('javascript')
<script type="text/javascript">
  $(function(e){

  $(document).on("focus", ".datepicker", function(){
    $(this).datetimepicker({
      timepicker:false,
      format:'Y-m-d'});
  });
  var table2 = $('.companies-table').DataTable({
    "sPaginationType": "listbox",
       processing: false,
        // "language": {
        //     processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      ordering: false,
      serverSide: true,
      ajax: {
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal").modal('show');
        },
        url: "{!! route('get-company') !!}"},
      columns: [
        { data: 'action', name: 'action' },
        { data: 'logo', name: 'logo' },
        { data: 'prefix', name: 'prefix' },
        { data: 'counter', name: 'counter' },
        { data: 'company_name', name: 'company_name' },
        { data: 'thai_billing_name', name: 'thai_billing_name' },
        { data: 'tax_id', name: 'tax_id' },
        { data: 'billing_email', name: 'billing_email' },
        { data: 'billing_phone', name: 'billing_phone' },
        { data: 'billing_fax', name: 'billing_fax' },
        { data: 'billing_address', name: 'billing_address' },
        { data: 'thai_billing_address', name: 'thai_billing_address' },
        { data: 'billing_city', name: 'billing_city' },
        { data: 'billing_state', name: 'billing_state' },
        { data: 'billing_country', name: 'billing_country' },
        { data: 'billing_zip', name: 'billing_zip' },
      ],
      drawCallback: function(){
        $('#loader_modal').modal('hide');
      },
  });

     $('.dataTables_filter input').unbind();
$('.dataTables_filter input').bind('keyup', function(e) {
if(e.keyCode == 13) {
  // alert();
        table2.search($(this).val()).draw();
}
});

  $(document).on('keyup', '.form-control', function(){
    $(this).removeClass('is-invalid');
    $(this).next().remove();
  });

  $(document).on('submit', '.add-company', function(e){
    e.preventDefault();
    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
     $.ajax({
        url: "{{ route('add-company') }}",
        method: 'post',
        data: new FormData(this),
        contentType: false,
        cache: false,
        processData:false,
        beforeSend: function(){
          $('.save-btn').val('Please wait...');
          $('.save-btn').addClass('disabled');
          $('.save-btn').attr('disabled', true);
        },
        success: function(result){
          $('.save-btn').val('add');
          $('.save-btn').attr('disabled', true);
          $('.save-btn').removeAttr('disabled');
          if(result.success === true){
            $('.modal').modal('hide');
            toastr.success('Success!', 'Courier added successfully',{"positionClass": "toast-bottom-right"});
            $('.add-company')[0].reset();
            setTimeout(function(){
              window.location.reload();
            }, 2000);

          }


        },
        error: function (request, status, error) {
              $('.save-btn').val('add');
              $('.save-btn').removeClass('disabled');
              $('.save-btn').removeAttr('disabled');
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

  $(document).on('click', '.edit-icon',function(e){
          var id = $(this).data('id');
          $.ajax({
             method: "get",
              data:{id:id},
              url:"{{ route('company-editing') }}",
              beforeSend: function(){
                $('#loader_modal').modal({
                  backdrop: 'static',
                  keyboard: false
                });
               $("#loader_modal").modal('show');
              },
              success: function(data)
              {
                $("#loader_modal").modal('hide');
                $('#editSupplierModalForm').html(data);
                $('#editSupplierModal').modal();
              }
          });
      });

  $(document).on('submit', '.edit_comp_form', function(e){
        e.preventDefault();
        $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.ajax({
            url: "{{ route('company-updating') }}",
            method: 'post',
            data: new FormData(this),
            contentType: false,
            cache: false,
            processData:false,
            beforeSend: function(){
              $('#edit_company').val('Please wait...');
              $('#edit_company').addClass('disabled');
              $('#edit_company').attr('disabled', true);
            },
            success: function(result){
              $('#edit_company').val('add');
              $('#edit_company').removeClass('disabled');
              $('#edit_company').removeAttr('disabled');
              if(result.success === true){
                // location.reload();
                $('.companies-table').DataTable().ajax.reload();
                $('.modal').modal('hide');

              }
            },
            error: function (request, status, error) {
              $('#edit_company').val('update');
              $('#edit_company').removeClass('disabled');
              $('#edit_company').removeAttr('disabled');
              $('.form-control').removeClass('is-invalid');
              $('.form-control').next().remove();
              json = $.parseJSON(request.responseText);
              $.each(json.errors, function(key, value){
                  $('select[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                  $('select[name="'+key+'"]').addClass('is-invalid');
                  $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                  $('input[name="'+key+'"]').addClass('is-invalid');
              });
            }
        });
    });

  // getting state with respect to selected country
  $(document).on('change',".country",function(){
    var country_id=$(this).val();
    var store_state =$(this);
    $.ajax({

        url:"{{url('common/filter-state')}}",
        method:"get",
        dataType:"json",
        data:{country_id:country_id},
        success:function(data){
          var html_string='';
          for(var i=0;i<data.length;i++){
              html_string+="<option value='"+data[i]['id']+"'>"+data[i]['name']+"</option>";
          }
          $("#state").html(html_string);
          $("#state2").html(html_string);
          $('.selectpicker').selectpicker('refresh');
        },
        error:function(){
          alert('Error');
        }
    });
});

  $(document).on('click', '.update_warehosues_on_product_level', function(e){
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    var timeout;
    $.ajax({
      url: "{{ route('update-warehouses-on-product-level') }}",
      method: 'post',
      dataType: 'json',
      beforeSend: function()
      {
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
        timeout = setTimeout(function(){
          var alertMsg = "<p style='color:red;''>Please be paitent this process will take some time .....</p>";
          $('#msg').html(alertMsg);
        }, 10000);
      },
      success: function(result)
      {
        clearTimeout(timeout);
        $('#loader_modal').modal('hide');
        $('#msg').empty();
        if(result.success == true)
        {
          toastr.success('Success!', 'Products Updated successfully',{"positionClass": "toast-bottom-right"});
        }
      },
      error: function (request, status, error)
      {
        $('#loader_modal').modal('hide');
        $('#msg').empty();
        toastr.error('Error!', 'Something Went Wrong',{"positionClass": "toast-bottom-right"});
      }
    });
  });

  });
</script>
@stop

