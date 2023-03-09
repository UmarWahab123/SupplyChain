@extends('users.layouts.layout')

@section('title','Products Management | Supply Chain')

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

{{-- Content Start from here --}}
<div class="row mb-3">
  <div class="col-md-8 title-col">
    <h3 class="maintitle">ADD PRODUCT FORM</h3>
  </div>
 
</div>


<div class="row entriestable-row mt-3">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
     <!-- Overview Row -->
 <div class="row add-gemstone">
          <div class="col-md-12">
            <div class="bg-white pr-4 pl-4 pb-5">
            <div class="row bulk-add-gemstone-col">
            <div class="col-12">
            <div class="bulk-add-gemstone bulk-add-gemstone-2">

             {!! Form::open(['method' => 'POST', 'id' => 'save_prod_btn' ,'class' => 'add-product-form', 'enctype' => 'multipart/form-data']) !!}

              <div class="form-row">
                  <div class="form-group col-4">
                      {!! Form::text('refrence_code', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Reference # | Leave Blank For System']) !!}
                  </div>
                  <div class="form-group col-4">
                    {!! Form::text('hs_code', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'autocomplete' => 'off', 'placeholder' => 'HS Number']) !!}
                  </div>

                  <div class="form-group col-4">
                    {!! Form::text('name', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Product Name']) !!}
                  </div>

                  <!-- product type commented -->
                  <!-- <div class="form-group col-4">
                  <select name="product_type_id" id="product" class="font-weight-bold form-control-lg form-control new_product_type">
                      <option value="" selected="" disabled="">Choose Product Type</option>
                      @if($products)
                      @foreach($products as $product)
                      <option value="{{$product->id}}">{{$product->title}}</option>
                      @endforeach
                      <option value="new_type">Add New Type</option>
                      @endif
                  </select>
                  </div> -->
              </div>              

              <div class="form-row">

                <div class="form-group col-4">
                  <select name="buying_unit" id="buying_unit" class="font-weight-bold form-control-lg form-control new_unit">
                      <option value="" selected="">Select Unit</option>
                      @if($units)
                      @foreach($units as $unit)
                      <option value="{{$unit->id}}">{{$unit->title}}</option>
                      @endforeach
                      <option value="new_unit">Add New Unit</option>
                      @endif
                  </select>
                </div>
                <div class="form-group col-4">
                      <input type="file" class="font-weight-bold form-control-lg form-control" id="product_image" name="product_image[]" multiple title="Choose Product Image Here">
                </div>
                <div class="form-group col-4">
                  {!! Form::number('quantity', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'QTY']) !!}
                </div>                
              </div>

              <div class="form-row">
                
                <div class="form-group col-4">
                  <select name="product_category" id="product_cat" class="font-weight-bold form-control-lg form-control new_product_cat get-sub-cat">
                      <option value="" selected="" disabled="">Choose @if(!array_key_exists('category', $global_terminologies)) Category @else {{$global_terminologies['category']}} @endif</option>
                      @if($categories)
                      @foreach($categories as $category)
                      <option value="{{$category->id}}">{{$category->title}}</option>
                      @endforeach
                      <!-- <option value="new_cat">Add New Category</option> -->
                      @endif
                  </select>
                </div>

                <div class="form-group col-4" id="sub_prod_cat">
                  
                </div>

              </div>

              <div class="form-row">
                <div class="form-group col-4">
                  {!! Form::textarea('short_desc', $value = null, ['class' => 'font-weight-bold form-control-lg-textarea form-control', 'placeholder' => 'Short Description of Product']) !!}
                </div>
                <div class="form-group col-8">
                  {!! Form::textarea('long_desc', $value = null, ['class' => 'font-weight-bold form-control-lg-textarea form-control', 'placeholder' => 'Long Description of Product']) !!}
                </div>
              </div>


              <input type="hidden" name="set_default_supplier" id="set_default_supplier" value="">


              <!-- here is the optional fields code starts -->
              <div class="col-12 form-group">
                    <a class="" style="color:red;cursor: pointer;font-weight: bold;" id="view_optional_field_btn">View Optional Fields</a>
              </div>

              <div id="optional_fields_div" style="display: none;">

                <div class="col-lg-12 col-md-6 col-sm-6 col-12" style="margin-top: 20px;">
                    <h3>Suppliers Information</h3>
                </div>

                <div class="form-row">
                <div class="form-group col-4">
                  <select name="default_supplier[]" id="supplier" class="font-weight-bold form-control-lg form-control choose-supplier default_supplier_id_get" disabled="">
                    <option>Choose Default @if(!array_key_exists('supplier', $global_terminologies)) Supplier @else {{$global_terminologies['supplier']}} @endif</option>
                    <option value="new">Add New</option>                    
                  </select>
                </div>

                <div class="form-group col-3">
                    {!! Form::number('import_tax[]', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Import Tax (Book)']) !!}
                </div>
                <div class="form-group col-3">
                  {!! Form::text('lead_time[]', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Leading Time']) !!}
                </div>
                <div class="form-group col-2">
                  {!! Form::number('buying_price[]', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Purchasing Price (EUR)']) !!}
                </div>
                </div>

              {{-- insert suppliers using jquery  --}}
                <div id="insert_supplier_div">
                 
                </div>    

                <div align="right" style="margin-bottom: 15px;">
                  <a class="btn-sm btn-success" type="button" id="add_supplier"><i class="fa fa-plus"></i></a> 
                  <a class="btn-sm btn-danger" type="button" id="remove_supplier"><i class="fa fa-minus"></i></a>
                </div>

                <!-- <div class="form-row">
                  <div class="form-group col-4">
                     {!! Form::number('import_tax_acutal', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Import Tax (Actual)']) !!}
                  </div>
                  <div class="form-group col-4">
                    {!! Form::number('freight', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Freight']) !!}
                  </div>
                  <div class="form-group col-4">
                      {!! Form::number('landing', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Landing']) !!}
                  </div>
                </div> -->

                <!-- <div class="form-row">
                <div class="form-group col-4">
                    {!! Form::number('unit_conversion_rate', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Unit Conversion Rate']) !!}
                </div>
                <div class="form-group col-4">
                  {!! Form::number('selling_price', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Selling Price']) !!}
                </div>
                <div class="form-group col-4">
                  {!! Form::number('weight', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Weight']) !!}
                </div>
              </div> -->

            </div>
                

              <div class="form-submit" align="right">
                <input type="submit" value="add" class="btn btn-bg save-btn">
              </div>
          {!! Form::close() !!}


            </div>
          </div>
          </div>
          
        </div>  

      </div>
   </div>


    
  </div>
    
  </div>
  </div>

</div>
<!--  Content End Here -->

{{-- Supplier Modal  --}}
<div class="modal" id="addSupplierModal">
    <div class="modal-dialog modal-lg modal-dialog-centered parcelpop">
      <div class="modal-content">   
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div> 
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Add New Supplier</h3>
          <div class="mt-4">
          {!! Form::open(['method' => 'POST', 'class' => 'add-supplier-form']) !!}
              <div class="form-row">
                  <div class="form-group col-12">
                    {!! Form::text('company', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Company (Required)']) !!}
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
                {!! Form::email('email', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Email']) !!}
              </div>
              <div class="form-group col-6">
                {!! Form::text('phone', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Phone']) !!}
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-6">
                {!! Form::select('country', $countries, null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Choose Country', 'id' => 'country']) !!}
              </div>
              <div class="form-group col-6 ">
                {!! Form::select('state', ['' => 'Choose State'], null, ['class' => 'font-weight-bold form-control-lg form-control fill_states_div', 'id' => 'state']) !!}
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
                {!! Form::text('city', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'City']) !!}
              </div>
              <div class="form-group col-6">
                {!! Form::text('postalcode', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Postal Code']) !!}
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-6">
                <select name="category_id" id="category" class="font-weight-bold form-control-lg form-control new_product_cat-supp">
                      <option value="" selected="" disabled="">Choose Category</option>
                      <!-- @if($categories)
                      @foreach($categories as $category)
                      <option value="{{$category->id}}">{{$category->title}}</option>
                      @endforeach -->
                      <!-- <option value="new_cat">Add New Category</option> -->
                      <!-- @endif -->
                  </select>
              </div>
              <div class="form-group col-6">
                {!! Form::select('credit_term', ['' => 'Choose a Credit Term','Net 30'=>'Net 30','Net 60'=>'Net 60','Net 90'=>'Net 90'], null, ['class' => 'font-weight-bold form-control-lg form-control', 'id' => 'credit_term']) !!}
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-6">
                <select name="product_type_id" id="product" class="font-weight-bold form-control-lg form-control new_product_type-supp">
                      <option value="" selected="" disabled="">Choose Product Type</option>
                     <!--  @if($products)
                      @foreach($products as $product)
                      <option value="{{$product->id}}">{{$product->title}}</option>
                      @endforeach -->
                      <!-- <option value="new_type">Add New Type</option> -->
                      <!-- @endif -->
                  </select>
              </div>
            </div>

            <div class="form-submit">
              <input type="submit" value="add" class="btn btn-bg save-btn-sup" id="save_sup_btn">
              <input type="reset" value="close" data-dismiss="modal" class="btn btn-danger close-btn">
            </div>
          {!! Form::close() !!}
         </div> 
        </div>
      </div>
    </div>
  </div>

<!--  Unit Modal Start Here -->
  <div class="modal fade" id="addunitModal">
    <div class="modal-dialog modal-dialog-centered parcelpop">
      <div class="modal-content">   
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div> 
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Add Unit</h3>
          <div class="mt-5">
          {!! Form::open(['method' => 'POST', 'class' => 'adding-unit-form']) !!}
            <div class="form-group">
              {!! Form::text('title', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'autocomplete' => 'off', 'placeholder' => 'Enter Unit']) !!}
            </div>
            
            <div class="form-submit">
              <input type="submit" value="add" class="btn btn-bg save-unit-btn" id="save-unit-btn">
              <input type="reset" value="close" class="btn btn-danger close-btn">
            </div>
          {!! Form::close() !!}
         </div> 
        </div>
      </div>
    </div>
  </div>
<!-- add Unit Modal End Here -->

<!--  Product Type Modal Start Here -->
  <div class="modal fade" id="addProdModal">
    <div class="modal-dialog modal-dialog-centered parcelpop">
      <div class="modal-content">   
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div> 
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Add Product Type</h3>
          <div class="mt-5">
          {!! Form::open(['method' => 'POST', 'class' => 'adding-prod-type-form']) !!}
            <div class="form-group">
              {!! Form::text('title', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'autocomplete' => 'off', 'placeholder' => 'Enter Product Type']) !!}
            </div>
            
            <div class="form-submit">
              <input type="submit" value="add" class="btn btn-bg save-prod-type-btn" id="save-prod-type-btn">
              <input type="reset" value="close" class="btn btn-danger close-btn">
            </div>
          {!! Form::close() !!}
         </div> 
        </div>
      </div>
    </div>
  </div>
<!-- add Product Type Modal End Here -->

<!--  Product Category Modal Start Here -->
  <div class="modal fade" id="addProdCatModal">
    <div class="modal-dialog modal-dialog-centered parcelpop">
      <div class="modal-content">   
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div> 
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Add Product Category</h3>
          <div class="mt-5">
          {!! Form::open(['method' => 'POST', 'class' => 'adding-prod-cat-form']) !!}
            <div class="form-group">
              {!! Form::text('title', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'autocomplete' => 'off', 'placeholder' => 'Enter Product Category']) !!}
            </div>
            
            <div class="form-submit">
              <input type="submit" value="add" class="btn btn-bg save-prod-cat-btn" id="save-prod-cat-btn">
              <input type="reset" value="close" class="btn btn-danger close-btn">
            </div>
          {!! Form::close() !!}
         </div> 
        </div>
      </div>
    </div>
  </div>
<!-- add Product Category Modal End Here -->

@endsection

@section('javascript')

<script type="text/javascript">
  $(function(e){

    // getting default supplier id and store in hidden field
    $(document).on('change', '.choose-supplier', function(e){
      var default_supplier_id  = $('.default_supplier_id_get option:selected').val();
      $('#set_default_supplier').val(default_supplier_id);

    });

    //same modal for adding supplier by set default supplier
    $(document).on('change', '.choose-supplier', function(e){
      // getting values and text of product type in case of adding new supplier
      var product_type_text = $('.new_product_type option:selected').text();
      var product_type_val  = $('.new_product_type option:selected').val();

      // getting values and text of categoty type in case of adding new supplier
      var category_type_text = $('.new_product_cat option:selected').text();
      var category_type_val  = $('.new_product_cat option:selected').val();

    if($(this).val() === 'new_supplier'){

      // new values selected append in supplier add modal
      $('.new_product_type-supp').html('<option value="'+product_type_val+'" selected>'+product_type_text+'</option>');
      $('.new_product_cat-supp').html('<option value="'+category_type_val+'" selected>'+category_type_text+'</option>');
      
      $('#addSupplierModal').modal('show');
    }
    });

    $(document).on('change', '.choose-supplier', function(e){
    if($(this).val() === 'new'){
      $('#addSupplierModal').modal('show');
    }
    });

    // getting product sub category
    $("#sub_prod_cat").hide();
    $(document).on('change', '.get-sub-cat', function(e){
      
      var cat_id = $(this).val();
      $("#sub_prod_cat").show();
      $.ajax({

        url:"{{route('get-product-sub-cat')}}",
        method:"get",
        dataType:"json",
        data:{cat_id:cat_id},
        success:function(data){   
           if(data['data'].length > 0)
           {


            var html_string ='<select name="product_category" id="product_category" class="font-weight-bold form-control-lg form-control product_sub_category">';
            html_string +='<option value="">Select Sub-Category</option>';
            for(var i=0;i<data['data'].length;i++){
                html_string+="<option value='"+data['data'][i]['id']+"'>"+data['data'][i]['title']+"</option>";
            }
            html_string+=" </select>";
            
            $("#sub_prod_cat").html(html_string);
            $('#sub_prod_cat').show();

          }
          else
          {
            $('#sub_prod_cat').hide();
          }
        },
        error:function(){
            alert('Error');
        }

    });
    });

    // optional field show button code
    $(document).on("click","#view_optional_field_btn",function(){
      $("#optional_fields_div").fadeToggle();
    });

    // adding Product Category code start here
    $(document).on('change', '.new_product_cat', function(e){
    if($(this).val() === 'new_cat'){
      $('#addProdCatModal').modal('show');
    }
    });

    $(document).on('click', '#save-prod-cat-btn', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('adding-product-cat') }}",
          method: 'post',
          data: $('.adding-prod-cat-form').serialize(),
          beforeSend: function(){
            $('.save-prod-cat-btn').val('Please wait...');
            $('.save-prod-cat-btn').addClass('disabled');
            $('.save-prod-cat-btn').attr('disabled', true);
          },
          success: function(result){
            $('.save-prod-cat-btn').val('add');
            $('.save-prod-cat-btn').attr('disabled', true);
            $('.save-prod-cat-btn').removeAttr('disabled');
            if(result.success === true){
              // $('.modal').modal('hide');
              let new_option = '<option selected value="'+result.prdouct_cat.id+'">'+result.prdouct_cat.title+'</option>';
              $(".new_product_cat option:last").before(new_option);
              toastr.success('Success!', 'Product Category Added Successfully',{"positionClass": "toast-bottom-right"});
              $('.adding-prod-cat-form')[0].reset();
              $('#addProdCatModal').modal('hide');
              
            }
            
            
          },
          error: function (request, status, error) {
                $('.save-prod-cat-btn').val('add');
                $('.save-prod-cat-btn').removeClass('disabled');
                $('.save-prod-cat-btn').removeAttr('disabled');
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
    // adding Product Category code ends here

    // adding Product type code start here
    $(document).on('change', '.new_product_type', function(e){
    if($(this).val() === 'new_type'){
      $('#addProdModal').modal('show');
    }
    });

    $(document).on('click', '#save-prod-type-btn', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('adding-product-type') }}",
          method: 'post',
          data: $('.adding-prod-type-form').serialize(),
          beforeSend: function(){
            $('.save-prod-type-btn').val('Please wait...');
            $('.save-prod-type-btn').addClass('disabled');
            $('.save-prod-type-btn').attr('disabled', true);
          },
          success: function(result){
            $('.save-prod-type-btn').val('add');
            $('.save-prod-type-btn').attr('disabled', true);
            $('.save-prod-type-btn').removeAttr('disabled');
            if(result.success === true){
              // $('.modal').modal('hide');
              let new_option = '<option selected value="'+result.prdouct_type.id+'">'+result.prdouct_type.title+'</option>';
              $(".new_product_type option:last").before(new_option);
              toastr.success('Success!', 'Product Type Added Successfully',{"positionClass": "toast-bottom-right"});
              $('.adding-prod-type-form')[0].reset();
              $('#addProdModal').modal('hide');
              
            }
            
            
          },
          error: function (request, status, error) {
                $('.save-prod-type-btn').val('add');
                $('.save-prod-type-btn').removeClass('disabled');
                $('.save-prod-type-btn').removeAttr('disabled');
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
    // adding Product type code ends here

    // adding unit code start here
    $(document).on('change', '.new_unit', function(e){
    if($(this).val() === 'new_unit'){
      $('#addunitModal').modal('show');
    }
    });

    $(document).on('click', '#save-unit-btn', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('adding-unit') }}",
          method: 'post',
          data: $('.adding-unit-form').serialize(),
          beforeSend: function(){
            $('.save-unit-btn').val('Please wait...');
            $('.save-unit-btn').addClass('disabled');
            $('.save-unit-btn').attr('disabled', true);
          },
          success: function(result){
            $('.save-unit-btn').val('add');
            $('.save-unit-btn').attr('disabled', true);
            $('.save-unit-btn').removeAttr('disabled');
            if(result.success === true){
              // $('.modal').modal('hide');
              let new_option = '<option selected value="'+result.unit.id+'">'+result.unit.title+'</option>';
              $(".new_unit option:last").before(new_option);
              toastr.success('Success!', 'Unit added successfully',{"positionClass": "toast-bottom-right"});
              $('.adding-unit-form')[0].reset();
              $('#addunitModal').modal('hide');
              
            }
            
            
          },
          error: function (request, status, error) {
                $('.save-unit-btn').val('add');
                $('.save-unit-btn').removeClass('disabled');
                $('.save-unit-btn').removeAttr('disabled');
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
      // adding unit code ends here

    $(document).on('keyup', '.form-control', function(){
      $(this).removeClass('is-invalid');
      $(this).next().remove();
    });    

    $(document).on('submit', '#save_prod_btn', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('add-product') }}",
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
              toastr.success('Success!', 'Product added successfully',{"positionClass": "toast-bottom-right"});
              $('.add-product-form')[0].reset();
              window.location.href = "{{ route('complete-list-product')}}";
            }
            else if(result.success === false)
            {
              $('.modal').modal('hide');
              toastr.success('Success!', 'Product added successfully',{"positionClass": "toast-bottom-right"});
              $('.add-product-form')[0].reset();
              window.location.href = "{{ route('incomplete-list-product')}}";
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

  });
</script>

<script type="text/javascript">
  $(document).on('click', '#save_sup_btn', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('adding-supplier') }}",
          method: 'post',
          data: $('.add-supplier-form').serialize(),
          beforeSend: function(){
            $('.save-btn-sup').val('Please wait...');
            $('.save-btn-sup').addClass('disabled');
            $('.save-btn-sup').attr('disabled', true);
          },
          success: function(result){
            $('.save-btn-sup').val('add');
            $('.save-btn-sup').attr('disabled', true);
            $('.save-btn-sup').removeAttr('disabled');
            if(result.success === true){
              // $('.modal').modal('hide');
              let new_option = '<option selected value="'+result.supplier.id+'">'+result.supplier.first_name+' '+result.supplier.last_name+'</option>';
              $(".choose-supplier option:last").before(new_option);
              // $(".set_default_supplier option:last").before(new_option);
              toastr.success('Success!', 'Supplier added successfully',{"positionClass": "toast-bottom-right"});
              $('.add-supplier-form')[0].reset();
              $('#addSupplierModal').modal('hide');
              
            }
            
            
          },
          error: function (request, status, error) {
                $('.save-btn-sup').val('add');
                $('.save-btn-sup').removeClass('disabled');
                $('.save-btn-sup').removeAttr('disabled');
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

  // for country onchange function
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
</script>

<script type="text/javascript">
  var count = 1;

  $(document).on("click","#add_supplier",function(){
  var html_string='';
  html_string+='<div>'
              +'<div class="form-row">'
                +'<div class="form-group col-4">'
                  +'<select name="default_supplier[]" id="supplier_'+count+'" class="font-weight-bold form-control-lg form-control choose-supplier">'
                    +'<option>Choose Supplier</option>';
                    html_string+='<option value="new">Add New</option>'                    
                  +'</select>'
                +'</div>'
                +'<div class="form-group col-3">'
                    +'{!! Form::number('import_tax[]', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Import Tax (Actual)']) !!}'
                +'</div>'
                +'<div class="form-group col-3">'
                  +'{!! Form::text('lead_time[]', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Leading Time']) !!}'
                +'</div>'
                +'<div class="form-group col-2">'
                  +'{!! Form::number('buying_price[]', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Purchasing Price (EUR) ']) !!}'
                +'</div>'
                +'</div>'
                +'</div>';

    $("#insert_supplier_div").append(html_string);
  
    var id = $('#product_cat').val();
    $.ajax({

        url:"{{url('get-supplier-by-id')}}"+'/'+id,
        method:"get",
        dataType: "json",

        success:function(data){
          prev = count-1;
          var x = 'supplier_'+prev; 
          // alert(x);
          $("#"+x).html('');
          $("#"+x).html(data.html1);

        },
        error:function(){
            alert('Error');
        }

    });    
count++;
  });

  $('#product_cat').on('change', function () {
    $('#add_supplier').prop('disabled', !$(this).val());
    $('#remove_supplier').prop('disabled', !$(this).val());
  }).trigger('change');

  $('#add_supplier').on('click', function () {
   var cat = $('#product_cat');
    if (cat.val() === '') {
        swal("Oops!", "Please Select The Product Category First!", "error");
    }
  });

  $(document).on("click","#remove_supplier",function(){
  $("#insert_supplier_div").children().last().remove();
  });

  // $("#set_default_supplier").hide();

  $(document).on("change","#product_cat",function(){

    var id = this.value;
    $.ajax({

        url:"{{url('get-supplier-by-id')}}"+'/'+id,
        method:"get",
        dataType: "json",
        success:function(data){

          $('#supplier').prop('disabled',false);
          $('#supplier').html(data.html2);
          // $("#set_default_supplier").show();
          // $('#set_default_supplier').html(data.html2);

        },
        error:function(){
            alert('Error');
        }

    });    
  });

  
</script>
@stop