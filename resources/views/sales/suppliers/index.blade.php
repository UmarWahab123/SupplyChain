@extends('sales.layouts.layout')

@section('title','Suppliers Management | Supply Chain')

@section('content')
@php
use App\Models\Common\ProductCategory;
@endphp
<style type="text/css">
.invalid-feedback {
     font-size: 100%; 
}
.disabled:disabled{
  opacity:0.5;
  cursor: not-allowed; 
}
</style>

{{-- Content Start from here --}}
<div class="row mb-0">
  
  <div class="col-md-10 title-col">
    <h3 class="maintitle text-uppercase fontbold mb-0 mt-1">SUPPLIERS CENTER</h3>
  </div>
</div>


<div class="row entriestable-row mt-3">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">

    <div class="table-responsive">
      <table class="table entriestable table-bordered table-suppliers text-center">
          <thead>
            <tr>
              <th>Action</th>  
              <th> {{$global_terminologies['company_name']}} </th>
              <th>Reference Name </th>
              <th>Country</th>
              <th>Contact Name</th>
              <th>Main Tags</th>
              <th>Supplier Since</th>
              <th> {{$global_terminologies['open_pos']}} </th>
              <th> {{$global_terminologies['total_pos']}} </th>
              <th>Last Order Date</th>
              <th>{{$global_terminologies['note_two']}}</th>
              <th>Status</th>
            </tr>
          </thead>
      </table>
    </div>  
    </div>
  </div>
</div>
</div>
<!--  Content End Here -->

<!--  Sales Modal Start Here -->
<div class="modal" id="addSupplierModal">
    <div class="modal-dialog modal-lg modal-dialog-centered parcelpop">
      <div class="modal-content">   
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">×</button>
        </div> 
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Add Supplier</h3>
          <div class="mt-4">
          {!! Form::open(['method' => 'POST', 'class' => 'add-supplier-form', 'enctype' => 'multipart/form-data']) !!}
              <div class="form-row">
                  <div class="form-group col-6">
                    {!! Form::text('company', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Company (Required)']) !!}
                  </div>
                  <div class="form-group col-6">
                    {!! Form::email('email', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Email']) !!}
                  </div>
              </div>

              <div class="form-row">
                  <div class="form-group col-12">
                      {!! Form::text('reference_number', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Reference Number | Leave Blank For System Generated']) !!}
                  </div>
              </div>

            <div class="form-row">
              <div class="form-group col-6">
                {!! Form::text('first_name', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'First Name']) !!}
              </div>
              <div class="form-group col-6">
                {!! Form::text('last_name', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Last Name']) !!}
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-6">
                {!! Form::text('phone', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Primary Phone']) !!}
              </div>
              <div class="form-group col-6">
                {!! Form::text('secondary_phone', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Additional Phone (Optional)']) !!}
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-6">
                {!! Form::select('country', $countries, null, ['class' => 'font-weight-bold form-control-lg form-control','required'=>'required', 'placeholder' => 'Choose Country', 'id' => 'country']) !!}
              </div>
              <div class="form-group col-6 ">
                {!! Form::select('state', ['' => 'Choose State'], null, ['class' => 'font-weight-bold form-control-lg form-control fill_states_div', 'required'=>'required', 'id' => 'state']) !!}
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-6">
                {!! Form::text('city', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'City']) !!}
              </div>
              <div class="form-group col-6">
                {!! Form::text('postalcode', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Postal Code']) !!}
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-6">
                {!! Form::text('address_line_1', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Address Line 1', 'maxlength' => 100]) !!}
              </div>
              <div class="form-group col-6">
                {!! Form::text('address_line_2', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Address Line 2 (optional)', 'maxlength' => 100]) !!}
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-6">
                {!! Form::select('credit_term', ['' => 'Choose a Credit Term','Net 30'=>'Net 30','Net 60'=>'Net 60','Net 90'=>'Net 90'], null, ['class' => 'font-weight-bold form-control-lg form-control', 'required'=>'required', 'id' => 'credit_term']) !!}
              </div>

              <div class="form-group col-6">
                <select name="category_id[]" id="category_id" class="font-weight-bold form-control-lg form-control parent_category" multiple="" required="">
                  <option disabled="" selected="">Choose Category</option>
                  @if($pcategory->count() > 0)
                  @foreach($pcategory as $cat)

                  <optgroup label="{{$cat->title}}">
                    @php
                      $getChild = ProductCategory::where('parent_id',$cat->id)->get();
                    @endphp
                    @foreach($getChild as $sub_cat)
                    <option value="{{$sub_cat->id}}">{{$sub_cat->title}}</option>
                    @endforeach
                  </optgroup>
                  
                  @endforeach
                  @endif
                </select>
              </div>
            </div>

            <div class="form-row">
            <div class="form-group col-6">
                <div class="custom-file mb-5">
                        <label class="d-block text-left">Logo (<span style="color:blue;">1MB is maximum file size </span>)</label>
                        
                       <input type="file" class="form-control form-control-lg" name="logo">
                      </div>
              </div>
            </div>

            {{--<div class="form-row">
                <div class="form-group col-6" id="sub_category">
                  <select name="category_id" id="category_id" class="font-weight-bold form-control-lg form-control sub_category">
                    <option>Choose Sub-Category</option>
                  </select>
              </div>
            </div>--}}

            <div class="form-submit">
              <input type="submit" value="add" class="btn btn-bg save-btn" id="save_cus_btn">
              <input type="reset" value="close" data-dismiss="modal" class="btn btn-danger close-btn">
            </div>
          {!! Form::close() !!}
         </div> 
        </div>
      </div>
    </div>
  </div>

<!-- Supplier Modal End Here -->

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

{{-- Supplier Notes Modal --}}
<div class="modal" id="notes-modal">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Supplier Notes</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">
          <div class="fetched-notes">
            <div class="adv_loading_spinner3 d-flex justify-content-center">
                <img class="img-spinner" src="{{ url('public/uploads/gif/waiting.gif') }}" style="margin-top: 10px;">
            </div>
          </div>
        </div>

      <div class="modal-footer">
       <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>


@endsection

@section('javascript')
<script type="text/javascript">
  $(function(e){

    $('.table-suppliers').DataTable({
         processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        ordering: false,
        "columnDefs": [
    { className: "dt-body-left", "targets": [ 1,2,3,4,5,6,8,9,10 ] },
    { className: "dt-body-right", "targets": [7] }
  ],
        serverSide: true,
        pageLength: {{100}},
        lengthMenu: [ 100, 200, 300, 400],
        ajax: "{!! url('sales/get-datatables-for-supplier') !!}",
        columns: [
            { data: 'action', name: 'action' },
            { data: 'company', name: 'company' },
            { data: 'reference_name', name: 'reference_name' },
            { data: 'country', name: 'country' },
            { data: 'name', name: 'name' },
            { data: 'product_type', name: 'product_type' },
            { data: 'created_at', name: 'created_at' },
            { data: 'open_pos', name: 'open_pos' },
            { data: 'total_pos', name: 'total_pos' },
            { data: 'last_order_date', name: 'last_order_date' },
            { data: 'notes', name: 'notes' },
            { data: 'status', name: 'status' },
        ]
    });

    $(document).on('keyup', '.form-control', function(){
      $(this).removeClass('is-invalid');
      $(this).next().remove();
    });    

    $(document).on('submit', '.add-supplier-form', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({

          url: "{{ url('sales/add-supplier') }}",
          method: 'post',
          data: new FormData(this), 
          contentType: false,       
          cache: false,             
          processData:false,
        
          // data: $('.add-supplier-form').serialize(),
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
              toastr.success('Success!', 'Supplier added successfully',{"positionClass": "toast-bottom-right"});
              $('.add-supplier-form')[0].reset();
              $('.table-suppliers').DataTable().ajax.reload();
              
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

    $(document).on('click', '.editIcon', function() {
          var id = $(this).data('id');
          $.ajax({
             method: "get",
              data:{id:id},
              url:"{{ route('supplier-edit') }}",
              success: function(data)
              {
                  $('#editSupplierModalForm').html(data);
                  $('#editSupplierModal').modal();
              }
          });
      });

    $(document).on('click', '#edit_cus_btn', function(e){
        e.preventDefault();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.ajax({
            url: "{{ route('update-supplier') }}",
            method: 'post',
            data: $('.edit_cus_form').serialize(),
            beforeSend: function(){
                $('#edit_cus_btn').val('Please wait...');
                $('#edit_cus_btn').addClass('disabled');
                $('#edit_cus_btn').attr('disabled', true);
            },
            success: function(result){
                $('#edit_cus_btn').val('add');
                $('#edit_cus_btn').removeClass('disabled');
                $('#edit_cus_btn').removeAttr('disabled');
                if(result.success === true){
                    $('.modal').modal('hide');
                    toastr.success('Success!', 'Supplier Updated successfully',{"positionClass": "toast-bottom-right"});
                    $('.edit_cus_form')[0].reset();
                    $('.table-suppliers').DataTable().ajax.reload();

                }


            },
            error: function (request, status, error) {
                $('#edit_cus_btn').val('update');
                $('#edit_cus_btn').removeClass('disabled');
                $('#edit_cus_btn').removeAttr('disabled');
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

  });
</script>

<script type="text/javascript">
  //This one is for the Create modal 
  $(document).on('change',"#country",function(){
    var country_id=$(this).val();
    var store_state =$(this);
    $.ajax({

        url:"{{url('common/filter-state')}}",
        method:"get",
        dataType:"json",
        data:{country_id:country_id},
        success:function(data){

            var html_string='<div id="state_div">   <label>@if(!array_key_exists('state', $global_terminologies)) State @else {{$global_terminologies['state']}} @endif</label>';
            html_string+='  <select id="state" name="state" class="form-control selectpicker" title="Choose State" data-live-search="true" data-select_type="state"><option>Select a State</option>';
            for(var i=0;i<data.length;i++){
                html_string+="<option value='"+data[i]['id']+"'>"+data[i]['name']+"</option>";
            }
            html_string+=" </select></div>";
            
            $("#state").html(html_string);
            $('.selectpicker').selectpicker('refresh');

        },
        error:function(){
            alert('Error');
        }

    });
});

//This one is for the Edit modal 
 $(document).on('change',"#edit_cus_country",function(){
    var country_id=$(this).val();
    var store_state =$(this);
    $.ajax({

        url:"{{url('common/filter-state')}}",
        method:"get",
        dataType:"json",
        data:{country_id:country_id},
        success:function(data){

            var html_string='<div id="state_div">   <label>@if(!array_key_exists('state', $global_terminologies)) State @else {{$global_terminologies['state']}} @endif</label>';
            html_string+='  <select id="state" name="state" class="form-control selectpicker" title="Choose State" data-live-search="true" data-select_type="state"><option>Select a State</option>';
            for(var i=0;i<data.length;i++){
                html_string+="<option value='"+data[i]['id']+"'>"+data[i]['name']+"</option>";
            }
            html_string+=" </select></div>";
            
            $("#edit_cus_state").html(html_string);
            $('.selectpicker').selectpicker('refresh');

        },
        error:function(){
            alert('Error');
        }

    });
});

 // delete Product
  $(document).on('click', '.deleteSupplier', function(e){

    var id = $(this).data('id');
      swal({
        title: "Are you sure?",
        text: "You want to delete this supplier ?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
        closeOnConfirm: true,
        closeOnCancel: false
        },
      function (isConfirm) {
          if (isConfirm) {
            $.ajax({
              method:"get",
              data:'id='+id,
              url: "{{ url('sales/delete-supplier') }}",
              success: function(response){
                if(response.success === true){
                  toastr.success('Success!', 'Supplier Deleted Successfully.',{"positionClass": "toast-bottom-right"});
                  $('.table-suppliers').DataTable().ajax.reload();
                }
              }
            });
          }
          else {
              swal("Cancelled", "", "error");
          }
      });
    });

  $(document).on('click', '.add-notes', function(e){
    var supplier_id = $(this).data('id');
    $('.note-supplier-id').val(supplier_id);
  });

    $(document).on('click', '.show-notes', function(e){
    let sid = $(this).data('id');
    $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
    $.ajax({
      type: "post",
      url: "{{ url('sales/get-supplier-note') }}",
      data: 'supplier_id='+sid,
      beforeSend: function(){
        var loader_img = "{{ url('public/uploads/gif/waiting.gif') }}";
        var loader_html = '<div class="d-flex justify-content-center"><img class="img-spinner" src="'+loader_img+'" style="margin-top: 10px;"></div>';
        $('.fetched-notes').html(loader_html);
      },
      success: function(response){
        $('.fetched-notes').html(response);
      }
    });

    }); 


  //This one is for the getting child of categories
  // $('#sub_category').hide();
  // $(document).on('change',".parent_category",function(){
  //   var category_id = $(this).val();
  //   $('#sub_category').show();
  //   $.ajax({

  //       url:"{{url('getting-product-category-childs-for-supplier')}}",
  //       method:"get",
  //       dataType:"json",
  //       data:{category_id:category_id},
  //       success:function(data){
            
  //           $(".sub_category").html(data.html_string);
  //           // $('.selectpicker').selectpicker('refresh');

  //       },
  //       error:function(){
  //           alert('Error');
  //       }

  //   });
  // });
</script>
@stop

