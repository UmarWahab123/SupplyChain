@extends('users.layouts.layout')

@section('title','Products Management | Supply Chain')

@section('content')


<div class="row mb-3">
  <div class="col-md-8 title-col">
    <h3 class="maintitle text-uppercase fontbold">Stock Adjustments</h3>
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
              <form action="{{route('get-filtered-stock-prod-excel')}}" method="POST" id="filteredProducts">
                {{csrf_field()}} 
              <div class="form-group row">
                <input type="hidden" name="warehouses" value="{{$warehouses->id}}">
                

                <div class="col-lg-3 col-md-6">
                  <div class="form-group">
                    <label>Choose @if(!array_key_exists('supplier', $global_terminologies)) Supplier @else {{$global_terminologies['supplier']}} @endif</label>
                    <select class="form-control selecting-suppliers" name="suppliers">
                        <option value="">Choose @if(!array_key_exists('supplier', $global_terminologies)) Supplier @else {{$global_terminologies['supplier']}} @endif</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{$supplier->id}}">{{@$supplier->reference_name}}</option>
                        @endforeach
                    </select>
                  </div>
                </div>

                <div class="col-lg-3 col-md-6">
                  <div class="form-group">
                    <label>Choose Primary Category</label>
                    <select class="form-control selecting-primary-cat" name="primary_category">
                        <option value="">Choose Primary Category</option>
                        @foreach($primary_category as $p_cat)
                        <option value="{{$p_cat->id}}">{{@$p_cat->title}}</option>
                        @endforeach
                    </select>
                  </div>
                </div>

                <div class="col-lg-3 col-md-6">
                  <div class="form-group">
                    <label>Choose Sub Category</label>
                    <select class="form-control fill_sub_cat_div" name="sub_category">
                        <option value="">Choose Sub Category</option>
                    </select>
                  </div>
                </div>

                <div class="col-lg-3 col-md-6">
                  <div class="form-group">
                    <label>Choose Type</label>
                    <select class="form-control product-types" name="types">
                        <option value="">Choose Type</option>
                        @foreach($types as $type)
                        <option value="{{$type->id}}">{{$type->title}}</option>
                        @endforeach
                    </select>
                  </div>
                </div>

                <div class="col pull-right">                    
                <button class="btn btn-success pull-right mt-4" id="filteredProductsbtn" >Download Filtered Products</button>
                </div>

              </div>  
              </form>

              <div class="form-group">
                <form action="{{route('get-all-prod-qty-excel')}}" method="POST" id="allProducts">
                  <input type="hidden" name="warehouses" id="warehouse_for_all">
                  {{csrf_field()}}     
                </form>
                <button class="btn btn-info pull-right d-none" id="alreadybtn" >Already Have File</button>
                <button class="btn btn-success pull-right d-none" id="allProductsbtn" >Download All Products</button>
              </div>
                    
              <br>
              <div class="upload-div" style="display: none;">
                <h3>Upload File</h3>
                <label><strong>Note : </strong>Please use the downloaded file for upload only.</label>
                <form action="{{route('bulk-upload-prod-qty')}}" class="upload-excel-form" method="post" enctype="multipart/form-data">
                  {{csrf_field()}}                  
                  <label for="bulk_import_file">Choose Excel File</label>
                  <input type="file" class="form-control" name="excel" id="price_excel" accept=".xls,.xlsx" required=""><br>
                  <button class="btn btn-info quantity-upload-btn" type="submit">Upload</button>
                </form>
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
  $(document).on('click','.quantity-upload-btn',function(){
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $("#loader_modal").modal('show');
  });

  $('#allProductsbtn').on('click',function (e) {
      // $('.upload-div').show(300);
      e.preventDefault();
      var w_id = $('select[name=warehouses]').val();
      $('#warehouse_for_all').val(w_id);
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $("#loader_modal").modal('show');
      $('#allProducts').submit();
    });

  $('#alreadybtn').on('click',function(){
    // $('.upload-div').show(300);
  });

  $('#filteredProductsbtn').on('click',function(e){
    var supplier_id = $('.selecting-suppliers').val();
    var primary_category = $('.selecting-primary-cat').val();
    var fill_sub_cat_div = $('.fill_sub_cat_div').val();
    var product_types = $('.product-types').val();
    if(supplier_id == '' && primary_category == '' && product_types == '')
    {
      swal('You Must Select A Supplier/Category/Type!');
      e.preventDefault();
      return false; 
    }
    else
    {
      $('#filteredProducts').submit();
    }
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

  $(document).on('change',".selecting-primary-cat",function(){
      var category_id=$(this).val();
      // var store_sb_cat =$(this);
      $.ajax({

          url:"{{route('filter-sub-category')}}",
          method:"get",
          dataType:"json",
          data:{category_id:category_id},
          success:function(data){

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
          error:function(){
              alert('Error');
          }

      });
    });

});
</script>
@endsection