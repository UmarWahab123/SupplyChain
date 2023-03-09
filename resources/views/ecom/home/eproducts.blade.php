@extends('ecom.layouts.layout')


@section('content')
<?php
use Carbon\Carbon;
?>
<style type="text/css">
.invalid-feedback {
     font-size: 100%;
}

.disabled:disabled{
  opacity:0.5;
  cursor: not-allowed;
}


.h-100{
  height: 145px !important;
}
.delete-btn-two{
  position: absolute;
  right: 30px;
  top: -10px;
}
th.hide_me, td.hide_me {display: none;}
/*table.dataTable thead .sorting { background: url('public/sort/sort_both.png') no-repeat center right;
  background-size: 5vh;}
table.dataTable thead .sorting_asc { background: url('public/sort/sort_asc.png') no-repeat center right;
  background-size: 5vh; }
table.dataTable thead .sorting_desc { background: url('public/sort/sort_desc.png') no-repeat center right;
  background-size: 5vh;}*/

</style>

{{-- Content Start from here --}}

<!-- Sales or Sales Coordinator or Warehouse -->
@if(Auth::user()->role_id == 3 || Auth::user()->role_id == 4 || Auth::user()->role_id == 6)
  @php
    $price_col_visibility = "class=noVis";
    $hide_pricing_columns = ', visible : false';
  @endphp
@endif

@if(Auth::user()->role_id == 6)
  @php
    $restaruant_price_col_visibility = "class=noVis";
    $restaruant_hide_pricing_columns = ', visible : false';
  @endphp
@endif

@if(@$hide_hs_description == 1)
  @php
    $hs_description_config = "class=noVis";
    $hs_description_column = ', visible : false';
  @endphp
@endif

  <!-- Header is here -->



  <div class="row align-items-center mb-3">
    <div class="col-lg-8 col-md-8 title-col">
      <h4 class="maintitle">ECOMMERCE PRODUCTS</h4>
    </div>
    <div class="col-lg-2 col-md-2">
      <input type="button" value="Price Check" class="btn recived-button price-check-btn d-none">
      <input type="button" value="Update Order Qty" class="btn recived-button update-billed-btn d-none">
    </div>
  </div>
  <div class="row align-items-center form-row mb-2">
    <div class="col-lg-2 col-md-3">
      <label for="">Supplier</label>
      <select class="font-weight-bold form-control-lg form-control js-states state-tags default_supplier" name="default_supplier">
        <option value="" selected="">Choose @if(!array_key_exists('supplier', $global_terminologies)) Supplier @else {{$global_terminologies['supplier']}} @endif</option>
        @foreach($suppliers as $supplier)
          @if(session('id'))
          <option {{$supplier->id == session('id') ? "selected" : ''}} value="{{$supplier->id}}">{{$supplier->reference_name}}</option>
          @else
          <option value="{{$supplier->id}}">{{$supplier->reference_name}}</option>
          @endif
        @endforeach
      </select>
    </div>
<div class="col-lg-3 col-md-3">
    <label for="">Primary {{$global_terminologies['category']}}</label>
      <select class="font-weight-bold form-control-lg form-control js-states state-tags prod_category_primary" name="prod_category_primary">
        <option value="" selected="">Choose Primary {{$global_terminologies['category']}}</option>
        @foreach($product_parent_categories as $product_parent_category)
            <option value="{{$product_parent_category->id}}" > {{$product_parent_category->title}} </option>
        @endforeach
      </select>
    </div>

    <div class="col-lg-3 col-md-3">
      <label for="">{{$global_terminologies['subcategory']}}</label>
      <select class="font-weight-bold form-control-lg form-control js-states state-tags prod_category" name="prod_category">
        <option value="" selected="">Choose {{$global_terminologies['subcategory']}}</option>
          @foreach($product_sub_categories as $pc_child)
            <option value="{{$pc_child->title}}" > {{$pc_child->title}} </option>
          @endforeach
      </select>
    </div>


    <div class="col-lg-2 col-md-3">
      <label for="">{{$global_terminologies['type']}}</label>
      <select class="font-weight-bold form-control-lg form-control js-states state-tags prod_type" name="prod_type bs2">
        <option value="" selected="">Choose {{$global_terminologies['type']}}</option>
        @foreach($product_types as $product_type)
              <option value="{{$product_type->id}}" > {{$product_type->title}} </option>
        @endforeach
      </select>
    </div>

    <!-- <div class="col-lg-2 col-md-3">
      <button type="button" style="margin-top: 20px"  class="btn recived-button reset-btn">Reset</button>
    </div> -->
    <div class="input-group-append ml-3 mt-4">
       <!--  <button class="btn recived-button reset" type="reset"> Reset         </button>   -->
        <span class="reset-btn common-icons" title="Reset">
          <img src="{{asset('public/icons/reset.png')}}" width="27px">
        </span>
      </div>


    {{--<div class="col-lg-2 col-md-3">
      <label for=""><b>Filter</b></label>
      <select class="font-weight-bold form-control-lg form-control js-states state-tags filter-dropdown" name="filter">
        <option value="" selected="">Select a Filter</option>
        <option value="stock">In Stock</option>
        <option value="reorder">Reorder Items</option>
      </select>
    </div>--}}
  </div>
 {{--<div class="row align-items-center mb-2">
    <div class="col-xl-2 col-lg-3 col">
      <button type="button"  class="btn recived-button reset-btn">Reset</button>
    </div>

</div>--}}
<div class="row entriestable-row">
  <div class="col-12">
    <div id="ordered_products_alert"></div>
  </div>
  <!-- End -->
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
      <div class="delete-selected-item catalogue-btn-group d-none">
      @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 2 || Auth::user()->role_id == 11)
        <a href="javascript:void(0);" class="btn selected-item-btn btn-sm deleteBtnImg deactivate-btn" data-toggle="tooltip" title="Deactivate Selected Items"><i class="fa fa-times"></i></a>
      @endif
      @if(Auth::user()->role_id == 9)
        <a href="javascript:void(0);" class="btn selected-item-btn btn-sm deleteBtnImg delete-btn" title="Unpublish Selected Items"><img src="{{asset('public/menu-icon/unpublished.png')}}" alt="" width='15' class="img-fluid"></a>

      @endif
      </div>
    <div class="table-responsive">
      <table class="table entriestable table-bordered table-product text-center purchase-complete-product" >
        <thead>
          <tr>
            <th class="noVis">
              <div class="custom-control custom-checkbox d-inline-block">
                <input type="checkbox" class="custom-control-input check-all" name="check_all" id="check-all">
                <label class="custom-control-label" for="check-all"></label>
              </div>
            </th>
            <th class="noVis">Action</th>
            <th><span id="pf_length">{{$global_terminologies['our_reference_number'] }}</span>
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="2">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="2">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th {{@$hs_description_config}}>HS Description</th>
            <th> @if(!array_key_exists('suppliers_product_reference_no', $global_terminologies)) Sup <br> Reference <br> # @else {{$global_terminologies['suppliers_product_reference_no']}} @endif</th>
            {{--<th class="">HS Code</th>--}}
            <th>{{$global_terminologies['category']}}/ {{$global_terminologies['subcategory']}}
            </th>
            <th class="nowrap">{{$global_terminologies['product_description']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="5">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="5">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{$global_terminologies['note_two']}}
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="6">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="6">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Picture</th>
            <th>Billed <br> Unit</th>
            <th>Selling <br> Unit</th>
            <th>{{$global_terminologies['type']}}
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="9">
              <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
            </span>
            <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="9">
              <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
            </span>
            </th>

            <th> {{$global_terminologies['brand']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="10">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="10">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>

            <th>{{$global_terminologies['temprature_c']}} </th>
            <th {{@$price_col_visibility}}>Import <br> Tax<br>(Book) %</th>
            <th>VAT</th>
            <th>Default/Last <br> Supplier
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="14">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="14">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{$global_terminologies['supplier_description']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="15">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="15">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>

            <th {{@$price_col_visibility}}>{{$global_terminologies['purchasing_price']}} <br>(EUR)</th>
            <th {{@$price_col_visibility}}>{{$global_terminologies['purchasing_price']}} <br> (THB) </th>
            <th {{@$price_col_visibility}}>Freight <br>Per Buying <br> Unit (THB)</th>
            <th {{@$price_col_visibility}}>Landing <br> Per Buying <br> Unit</th>
            <th {{@$price_col_visibility}}>{{$global_terminologies['cost_price']}}</th>
            <th id="unit_con">{{$global_terminologies['unit_conversion_rate']}}</th>
            <th {{@$price_col_visibility}}>{{$global_terminologies['net_price']}} <br>/unit (THB) </th>
            <th id='avg_length'>{{$global_terminologies['avg_units_for-sales']}}</th>
            <th id="exp_length">{{$global_terminologies['expected_lead_time_in_days']}}</th>
            <th>Last Update Price </th>
            <th>Total Visible Stock</th>
            <th>On Water</th>
            @php $i = 30; @endphp
            @if($getWarehouses->count() > 0)
              <th>{{$getWarehouses->warehouse_title}}<br>{{$global_terminologies['current_qty']}}
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="{{$i}}">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="{{$i}}">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
              </th>
              <th>{{$getWarehouses->warehouse_title}}<br> Available <br>QTY </th>

              <th>{{$getWarehouses->warehouse_title}}<br> Reserved <br>QTY </th>
            @endif

            @php $j = 0; @endphp
            @if($getCategories->count() > 0)
            @foreach($getCategories as $cat)
              <th>{{$cat->title}}<br>( Fixed Price )
              </th>
            @php $j++; @endphp
            @endforeach
            @endif

            @if($getCategoriesSuggested->count() > 0)
            @foreach($getCategoriesSuggested as $cat)
              <th>{{$cat->title}}<br>( Suggested Price )
              </th>
            @php $j++; @endphp
            @endforeach
            @endif
          </tr>
        </thead>
        @if($total_system_units == 1)
        <tfoot align="right">
          <tr>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            @if($getWarehouses->count() > 0)
            <th></th>
            <th></th>
            <th></th>
            @endif

            @foreach($getCategories as $cat)
            <th></th>
            @endforeach

            @foreach($getCategoriesSuggested as $cat)
            <th></th>
            @endforeach
          </tr>
        </tfoot>
        @endif
      </table>
    </div>
  </div>
  </div>
</div>



{{-- Upload excel file --}}

<div class="modal fade" id="uploadExcel">
  <div class="modal-dialog modal-dialog-centered parcelpop">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
      </div>
      <div class="modal-body text-center">
        <h3 class="text-capitalize fontmed">Upload Excel</h3>
        <div class="mt-3">
          <form method="post" action="{{url('upload-bulk-product')}}" class="upload-excel-form" enctype="multipart/form-data">
            {{csrf_field()}}

            <div class="form-group">
              <a href="{{asset('public/site/assets/purchasing/product_excel/Bulk_Products.xlsx')}}" download><span class="btn btn-success" id="examplefilebtn">Download Example File</span></a>
            </div>

            <div class="form-group">
              <input type="file" name="excel" class="font-weight-bold form-control-lg form-control" required="">
            </div>

            <div class="form-submit">
              <input type="submit" value="upload" class="btn btn-bg save-btn">
              <input type="reset" value="close" class="btn btn-danger close-btn">
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>


{{-- Product Images Modal --}}
<div class="modal" id="images-modal">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Product Images</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="row fetched-images">
          </div>
        </div>
        <div class="modal-footer">
         <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
        </div>
    </div>
  </div>
</div>

<!-- Modal For Image Uploading -->
<div class="modal" id="productImagesModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Add Product Images</h4>
        <button type="button" class="close close-btn" data-dismiss="modal">&times;</button>
      </div>
      <!-- Modal body -->
      <form role="form" class="add-prodimage-form" method="post" enctype="multipart/form-data">
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12 text-center">
            <div class="row">
              <div class="col-xs-12 col-md-12">
                <div class="col-md-12 col-lg-12 col-xs-12" id="columns">
                  <h3 class="form-label">Select the images</h3>
                  <div class="desc"><p class="text-center">or drag to box</p></div>
                  <div id="uploads" class="row"><!-- Upload Content --></div>
                </div>
                <div class="clearfix"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal footer -->
      <div class="modal-footer">
        <input type="hidden" name="product_id" class="img-product-id">
        <button class="btn btn-danger" id="reset" type="button" ><i class="fa fa-history"></i> Clear</button>
        <button class="btn btn-success" type="submit" class="save-btn" ><i class="fa fa-upload"></i> Upload </button>
        <button type="button" class="btn btn-primary" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
      </div>
     </form>

    </div>
  </div>
</div>
<!--  Content End Here -->

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

{{-- Add Supplier for dropdown add new modal --}}
<div class="modal addSupplierModalDropDown" id="addSupplierModalDropDown">
  <div class="modal-dialog">
  <div class="modal-content">

  <div class="modal-header">
    <h4 class="modal-title" id="headingSupplier"></h4>
    <button type="button" class="close" data-dismiss="modal">&times;</button>
  </div>

  <form id="addSupplierModalDropDownForm" method="POST">

    <div class="modal-body">

      <input type="hidden" name="product_id" value="">

      @php
      $getSuppliers = App\Models\Common\Supplier::where('status',1)->get();
      @endphp

      <div class="form-row">
        <div class="form-group col-6">
          <label class="pull-left">Supplier <b style="color: red;">*</b></label>
          <input type="hidden" name="selected_supplier_id" class="selected_supplier_id" id="selected_supplier_id" value="">
          <input type="text" class="font-weight-bold form-control-lg form-control addSuppDropDown" name="supplier" readonly="" value="">
        </div>

        <div class="form-group col-6">
          <label class="pull-left">{{$global_terminologies['suppliers_product_reference_no']}} <b style="color: red;">*</b></label>
          <input class="font-weight-bold form-control-lg form-control" placeholder="Product Supplier Ref#." name="product_supplier_reference_no" type="text">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-6">
          <label class="pull-left">Import Tax Actual </label>
          <input class="font-weight-bold form-control-lg form-control" placeholder="Import Tax Actual" name="import_tax_actual" type="number">
        </div>

        <div class="form-group col-6">
          <label class="pull-left">{{$global_terminologies['gross_weight']}} <b style="color: red;">*</b></label>
          <input class="font-weight-bold form-control-lg form-control" placeholder="Gross Weight" name="gross_weight" type="number">
        </div>
      </div>

      <div class="form-row">
      <div class="form-group col-6">
        <label class="pull-left">{{$global_terminologies['freight_per_billed_unit']}}</label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="Freight" name="freight" type="number">
      </div>

      <div class="form-group col-6">
        <label class="pull-left">{{$global_terminologies['landing_per_billed_unit']}}</label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="Landing" name="landing" type="number">
      </div>
      </div>

      <div class="form-row">
        <div class="form-group col-6">
          <label class="pull-left">{{$global_terminologies['purchasing_price']}} (EUR)  <b style="color: red;">*</b></label>
          <input class="font-weight-bold form-control-lg form-control" placeholder="Purchasing Price (EUR) " name="buying_price" type="number">
        </div>

        <div class="form-group col-6">
          <label class="pull-left">Leading Time</label>
          <input class="font-weight-bold form-control-lg form-control" placeholder="leading time e.g 2 days" name="leading_time"  type="number">
        </div>
      </div>

    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary save-prod-sup-drop-down" id="addSupplierBtnDropDown">Add</button>
    </div>
  </form>

  </div>
  </div>
</div>

<form id="export_complete_products_form" >
  <input type="hidden" name="default_supplier_exp" id="default_supplier_exp">
  <input type="hidden" name="prod_category_primary_exp" id="prod_category_primary_exp">
  <input type="hidden" name="prod_category_exp" id="prod_category_exp">
  <input type="hidden" name="prod_type_exp" id="prod_type_exp">
  <input type="hidden" name="filter-dropdown_exp" id="filter-dropdown_exp">
  <input type="hidden" name="type" id="type" value=1>
  <input type="hidden" name="search_value" id="search_value">

</form>

@endsection

 @php
      $hidden_by_default = '';
 @endphp
@section('javascript')
@if(Auth::user()->role_id == 3 || Auth::user()->role_id == 4 || Auth::user()->role_id == 6 || Auth::user()->role_id == 7 )
  <script type="text/javascript">
    $( document ).ready(function() {
      $('.inputDoubleClick').removeClass('inputDoubleClick');
      $('.selectDoubleClick').removeClass('selectDoubleClick');
      $('.prodSuppInputDoubleClick').removeClass('prodSuppInputDoubleClick');
      $('.inputDoubleClickFirst').removeClass('inputDoubleClickFirst');
      $('.inputDoubleClickFixedPrice').removeClass('inputDoubleClickFixedPrice');
      $('.selectDoubleClickPM').removeClass('selectDoubleClickPM');
      $('.inputDoubleClickPM').removeClass('inputDoubleClickPM');
      $('.market_price_check').attr('disabled',true);
      $('#add-product-image-btn').hide();
      $('#add-cust-fp-btn').hide();
      $('.add-supplier').hide();
    });
  </script>
@endif
<script type="text/javascript">

  // Customer Sorting Code Here
  var order = 1;
  var column_name = '';

  $('.sorting_filter_table').on('click',function(){
    $('.arrow_up').children('img').attr("src","{{ url('public/svg/up.svg') }}");
    $('.arrow_down').children('img').attr("src","{{ url('public/svg/down.svg') }}");

    order = $(this).data('order');
    column_name = $(this).data('column_name');

    $('.table-product').DataTable().ajax.reload();

    if($(this).data('order') ==  '2')
    {
      $(this).next('.sorting_filter_table').children('img').attr("src","{{ url('public/svg/not_active_down.svg') }}");
      $(this).children('img').attr("src","{{ url('public/svg/up.svg') }}");
    }
    else if($(this).data('order') == '1')
    {
      $(this).prev('.sorting_filter_table').children('img').attr("src","{{ url('public/svg/not_active_up.svg') }}");
      $(this).children('img').attr("src","{{ url('public/svg/down.svg') }}");
    }
  });
  $(document).ready(function(){
    var ref = '{{$global_terminologies['our_reference_number']}}';
    var words = $.trim(ref).split(" ");
    var newName = [];
    if(words.length > 1){
      for(var i=0; i< words.length; i++){
        if(i > 0){
          newName.push("<br />");
        }
        newName.push(words[i]);
      }
      ref = newName.join("");
    }
    $('#pf_length').html(ref);

    var avg_weight = '{{$global_terminologies['avg_units_for-sales']}}';
    var word = $.trim(avg_weight).split(" ");
    var newWord = [];
    if(word.length > 1){
      for(var i=0; i< word.length; i++){
        if(i > 0 && (i%3) == 0){
          newWord.push("<br />");
        }
        newWord.push(word[i]);
      }
      avg_weight = newWord.join(" ");
    }
    $('#avg_length').html(avg_weight);

    var exp_lead = '{{$global_terminologies['expected_lead_time_in_days']}}';
    var wordss = $.trim(exp_lead).split(" ");
    var newWords = [];
    if(wordss.length > 1){
      for(var i=0; i< wordss.length; i++){
        if(i > 0 && (i%2) == 0){
          newWords.push("<br />");
        }
        newWords.push(wordss[i]);
      }
      exp_lead = newWords.join(" ");
    }
    $('#exp_length').html(exp_lead);

    var unit_conv = '{{$global_terminologies['unit_conversion_rate']}}';
    var word_unit = $.trim(unit_conv).split(" ");
    var new_unit = [];
    if(word_unit.length > 1){
      for(var i=0; i< word_unit.length; i++){
        if(i > 0){
          new_unit.push("<br />");
        }
        new_unit.push(word_unit[i]);
      }
      unit_conv = new_unit.join("");
    }
    $('#unit_con').html(unit_conv);
  });

  $(function(e){
    $(".state-tags").select2();
    var prod_category;
    var className;
    if ($('.prod_category_primary option:selected').val() == '' && $('.prod_category option:selected').val() == '') {
      prod_category = '';
      className = '';
    }
    else if ($('.prod_category option:selected').val() != '') {
      prod_category = 'cus-' + $('.prod_category option:selected').val();
      className = 'child';
    }
    else{
      prod_category = 'cus-' + $('.prod_category_primary option:selected').val();
      className = 'parent';
    }
    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
    var table2 = $('.table-product').DataTable({
      "sPaginationType": "listbox",
    searching:true,
    processing: false,
    "language": {
    processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    colReorder: {
      realtime: false
    },
    ordering: false,
    // "aaSorting": [[2,5,14,24]],
    retrieve: true,
    scrollX: true,
    scrollY : '90vh',
    scrollCollapse: true,

    serverSide: true,
    fixedHeader: false,
    dom: 'Blfrtip',
    "columnDefs": [
      { targets: [{{ ($table_hide_columns != null) ? $table_hide_columns->hide_columns : $hidden_by_default }}], visible: false },
      // { className: "dt-body-left", "targets": [ 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,24,26,27,28,29,30,31,32,33] },
      // { className: "dt-body-right", "targets": [18,19,20,21,22,23,25 ] },

    ],
    pageLength: {{50}},
    lengthMenu: [ 50, 100, 150, 200],
    buttons: [
      {
        extend: 'colvis',
        columns: ':not(.noVis)',
      }
      // {
      //   extend: 'excelHtml5',
      //   text: '<i class="fa fa-file-excel-o" style="font-size:22px;" title="Export Excel"></i>',
      //   exportOptions: { orthogonal: 'export',columns: ':visible' },
      //   title: null,
      // }
    ],
    ajax: {
      beforeSend: function(){
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
        $("#loader_modal").modal('show');
      },
      // url:"{!! route('get-ecom-product') !!}",
      url:"{!! route('get-product') !!}",
      data: function(data) { data.default_supplier = $('.default_supplier option:selected').val(), data.prod_type = $('.prod_type option:selected').val(),
          data.prod_category = prod_category,
          data.className = className,
          data.ecomFilter = 'ecom-enabled'
          // data.prod_category = $('.prod_category option:selected').val(),
          // data.prod_category_primary = $('.prod_category_primary option:selected').val(),
          data.filter = $('.filter-dropdown option:selected').val(),
          data.sortbyparam = column_name,
          data.sortbyvalue = order },
      method: "post",
    },
      columns: [
        { data: 'checkbox', name: 'checkbox'{{@$hide_pricing_columns}} },
        { data: 'action', name: 'action', searchable: false, orderable: false, visible: false},
        { data: 'refrence_code', name: 'refrence_code' },
        { data: 'hs_description', name: 'hs_description', searchable: false, orderable: false {{@$hs_description_column}}},
        { data: 'p_s_reference_number', name: 'p_s_reference_number'},
        // { data: 'hs_code', name: 'hs_code' },
        { data: 'category_id', name: 'category_id'},
        { data: 'short_desc', name: 'short_desc'},
        { data: 'product_notes', name: 'product_notes'},
        { data: 'image', name: 'image'},
        { data: 'buying_unit', name: 'buying_unit'},
        { data: 'selling_unit', name: 'selling_unit' },
        // new added
        { data: 'product_type', name: 'product_type' },
        { data: 'brand', name: 'brand' },
        { data: 'product_temprature_c', name: 'product_temprature_c'{{@$hide_pricing_columns}}},

        { data: 'import_tax_book', name: 'import_tax_book'{{@$hide_pricing_columns}}},
        { data: 'vat', name: 'vat' },
        { data: 'supplier_id', name: 'supplier_id' },
        { data: 'supplier_description', name: 'supplier_description' },
        { data: 'vendor_price', name: 'vendor_price'{{@$hide_pricing_columns}}},
        { data: 'vendor_price_in_thb', name: 'vendor_price_in_thb'{{@$hide_pricing_columns}}},
        { data: 'freight', name: 'freight'{{@$hide_pricing_columns}}},
        { data: 'landing', name: 'landing'{{@$hide_pricing_columns}}},
        { data: 'total_buy_unit_cost_price', name: 'total_buy_unit_cost_price'{{@$hide_pricing_columns}}},
        { data: 'unit_conversion_rate', name: 'unit_conversion_rate' },
        { data: 'selling_unit_cost_price', name: 'selling_unit_cost_price'{{@$hide_pricing_columns}}},
        { data: 'weight', name: 'weight' },
        { data: 'lead_time', name: 'lead_time' },
        { data: 'last_price_history', name: 'last_price_history' },
        { data: 'total_visible_stock', name: 'total_visible_stock' },
        { data: 'on_water', name: 'on_water' },
        @if($getWarehouses->count() > 0)
          { data: '{{$getWarehouses->warehouse_title}}{{"current"}}', name: '{{$getWarehouses->warehouse_title}}{{"current"}}'},
          { data: '{{$getWarehouses->warehouse_title}}{{"available"}}', name: '{{$getWarehouses->warehouse_title}}{{"available"}}'},

          { data: '{{$getWarehouses->warehouse_title}}{{"reserve"}}', name: '{{$getWarehouses->warehouse_title}}{{"reserve"}}'},
        @endif

        // Dynamic columns start
        @if($getCategories->count() > 0)
        @foreach($getCategories as $cat)
          { data: '{{$cat->title}}', name: '{{$cat->title}}'},
        @endforeach
        @endif
        // Dynamic columns end

        // Dynamic columns start
        @if($getCategoriesSuggested->count() > 0)
        @foreach($getCategoriesSuggested as $cat)
          { data: 'suggest_{{$cat->title}}', name: 'suggest_{{$cat->title}}'},
        @endforeach
        @endif
        // Dynamic columns end
        ],
      initComplete: function () {
        // Enable THEAD scroll bars
        $('.dataTables_scrollHead').css('overflow', 'auto');
        $('.dataTables_scrollHead').on('scroll', function () {
          $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
        });

         @if(@$display_prods)
           table2.colReorder.order( [{{@$display_prods->display_order}}]);
         @endif
          // When Sales/Sales Coordinator and Warehouse User is logged In He/She Can't Edit Product Detail
        @if(Auth::user()->role_id == 3 || Auth::user()->role_id == 4 || Auth::user()->role_id == 6 )
          $('.inputDoubleClick').removeClass('inputDoubleClick');
        @endif

      },
      drawCallback: function(){
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        $('#loader_modal').modal('hide');

        var api = this.api()
        var json = api.ajax.json();

        // var unit_title = json.title;
        var total_unit = json.total_unit;
        // alert(total_unit);
        if(total_unit != 0)
        {
          $('#total_unit').html(total_unit);
        }
        else
        {
          $('#total_unit').html(0.00);
        }

      },
    });


      $('.dataTables_filter input').unbind();
    $('.dataTables_filter input').bind('keyup focusout', function(e) {
      let searchSession;
      let searchField;
      let count;
     searchField=$(this).val();
     searchField=searchField.trim();
     $('#tableSearchField').val(searchField);
     count=searchField.length;
      if(e.keyCode == 13) {

        table2.search($(this).val()).draw();
        return;
      }else if(count>0){
        if(e.type == 'focusout'){
           table2.search(this.value).draw();
              return;
                   }
        }else if( searchField==""){
                 $('input[type=search]').empty();
                 return;
        }
      });

    table2.on( 'column-visibility.dt', function ( e, settings, column, state ) {

      var arr = table2.colReorder.order();
      // var all = arr.split(',');
      var all = arr;
      if(all == ''){
        var col = column;
      }else{
        var col = all[column];
      }
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.post({
        url : "{{ route('toggle-column-display') }}",
        dataType : "json",
        data : {type:'ecom_completed_products',column_id:col},
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(data){
          $('#loader_modal').modal('hide');
          if(data.success == true){
            /*toastr.success('Success!', 'Product Column hidden/visible successfully.' ,{"positionClass": "toast-bottom-right"});*/
            // table2.ajax.reload();
          }
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
    });

    table2.on( 'column-reorder', function ( e, settings, details ) {
       $.get({
         url : "{{ route('column-reorder') }}",
         dataType : "json",
         data : "type=ecom_completed_products&order="+table2.colReorder.order(),
         beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
         },
         success: function(data){
          $('#loader_modal').modal('show');
         },
         error: function(request, status, error){
            $("#loader_modal").modal('hide');
          }
       });
           //console.log(table.colReorder.order());
       table2.button(0).remove();
       table2.button().add(0,
       {
         extend: 'colvis',
         autoClose: false,
         fade: 0,
         columns: ':not(.noVis)',
         colVis: { showAll: "Show all" }
       });
       // table2.ajax.reload();
       var headerCell = $( table2.column( details.to ).header() );
       headerCell.addClass( 'reordered' );
     });

    // dropdown double click editable code start here
  $(document).on('change', 'select.select-common', function(){

    if($(this).val() !== '')
    {
    if($(this).attr('name') == 'supplier_id')
    {
      var old_value = $('.inc-fil-supp').prev().data('fieldvalue');
      var pId = $(this).parents('tr').attr('id');
      var new_value = $("option:selected", this).html();
      $(this).removeClass('active');
      $(this).addClass('d-none');
      $(this).prev().removeClass('d-none');
      $(this).prev().html(new_value);
      $(this).prev().css("color", "");
      saveProdData(pId, $(this).attr('name'), $(this).val(), old_value);
    }
    else if($(this).attr('name') == 'category_id')
    {
      var old_value = $('.inc-fil-cat').prev().data('fieldvalue');
      var thisPointer= $(this);
      var pId = $(this).parents('tr').attr('id');
      swal({
        title: "Are you sure?",
        text: "Are you sure you want to update the Category of this product? This will update its Reference #, and their prices",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, Update it!",
        cancelButtonText: "Cancel",
        closeOnConfirm: true,
        closeOnCancel: true
        },
        function (isConfirm) {
          if(isConfirm)
          {
            var new_value = $("option:selected", thisPointer).html();
            thisPointer.addClass('d-none');
            thisPointer.prev().removeClass('d-none');
            thisPointer.prev().html(new_value);
            saveProdData(pId, thisPointer.attr('name'), thisPointer.val(), old_value);
          }
          else
          {
            $('.table-product').DataTable().ajax.reload();
          }
        }

      );

    }
    else
      {
        var old_value = $(this).prev().data('fieldvalue');
        var pId = $(this).parents('tr').attr('id');
        var new_value = $("option:selected", this).html();
        $(this).removeClass('active');
        $(this).addClass('d-none');
        $(this).prev().removeClass('d-none');
        $(this).prev().html(new_value);
        $(this).prev().css("color", "");
        saveProdData(pId, $(this).attr('name'), $(this).val(), old_value);
      }
    }
  });

    // to make fields double click editable
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
          thisPointer.val(fieldvalue);
          thisPointer.removeClass('active');
          thisPointer.prev().removeClass('d-none');
    }

    var fieldvalue = $(this).prev().data('fieldvalue');
    var new_value = $(this).val();

    if( (e.keyCode === 13 || e.which === 0) && $(this).hasClass('active')){

    var old_value = $(this).prev().data('fieldvalue');
    var pId = $(this).parents('tr').attr('id');

    if(fieldvalue == new_value)
    {
      var thisPointer = $(this);
      thisPointer.addClass('d-none');
      thisPointer.removeClass('active');
      thisPointer.prev().removeClass('d-none');
    }
    else
    {
      if($(this).attr('name') == 'refrence_code')
      {
        if($(this).val().length > 15)
        {
          swal({ html:true, title:'Alert !!!', text:'<b>Maximum 15 characters allowed for Refrence #</b>'});
          return false;
        }
        var new_value = $(this).val();
        $(this).prev().removeData('fieldvalue');
        $(this).prev().data('fieldvalue', new_value);
        $(this).attr('value', new_value);

        $(this).removeClass('active');
        $(this).addClass('d-none');
        $(this).prev().removeClass('d-none');
        $(this).prev().html(new_value);
        $(this).prev().css("color", "");
        saveProdData(pId, $(this).attr('name'), $(this).val(), old_value);
      }
      if($(this).attr('name') == 'product_fixed_price')
      {
        var new_value = $(this).val();
        if(new_value == '')
        {
          return false;
        }
        $(this).prev().removeData('fieldvalue');
        $(this).prev().data('fieldvalue', new_value);
        $(this).attr('value', new_value);

        $(this).removeClass('active');
        $(this).addClass('d-none');
        $(this).prev().removeClass('d-none');
        $(this).prev().html(new_value);
        saveProdData(pId, $(this).attr('name'), $(this).val(), old_value);
      }
      else if($(this).val() !== '' && $(this).hasClass('active'))
      {
        var new_value = $(this).val();
        $(this).prev().removeData('fieldvalue');
        $(this).prev().data('fieldvalue', new_value);
        $(this).attr('value', new_value);

        $(this).removeClass('active');
        $(this).addClass('d-none');
        $(this).prev().removeClass('d-none');
        $(this).prev().html(new_value);
        $(this).prev().css("color", "");
        saveProdData(pId, $(this).attr('name'), $(this).val(), old_value);
      }
    }
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

    $(document).on('change','.default_supplier',function(){
      $("#default_supplier_exp").val($('.default_supplier option:selected').val());
      var selected = $(this).val();
      // if($('.default_supplier option:selected').val() != '')
      // {
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
        $('.table-product').DataTable().ajax.reload();
      // }
  });

    $(document).on('change','.prod_type',function(){
      $("#prod_type_exp").val($('.prod_type option:selected').val());
      var selected = $(this).val();
      // alert(selected);
      // if($('.prod_type option:selected').val() != '')
      // {
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
        $('.table-product').DataTable().ajax.reload();
      // }
  });

    $(document).on('change','.prod_category',function(){
      $("#prod_category_exp").val($('.prod_category option:selected').val());
      var selected = $(this).val();
      // if($('.prod_category option:selected').val() != '')
      // {
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
        $('.table-product').DataTable().ajax.reload();
      // }
  });

    $(document).on('change','.prod_category_primary',function(){
      $("#prod_category_primary_exp").val($('.prod_category_primary option:selected').val());
      var category_id = $(this).val();
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $('#loader_modal').modal('show');

      /*** Code to Filter for Sub Category***/
      // $.ajax({
      //   url:"{{route('filter-sub-category')}}",
      //   method:"get",
      //   dataType:"json",
      //   data:{category_id:category_id},
      //   success:function(data)
      //   {
      //     var html_string = '';
      //     html_string+="<option value=''>Choose Subcategory</option>";
      //     for(var i=0;i<data.length;i++)
      //     {
      //       html_string+="<option value='"+data[i]['id']+"'>"+data[i]['title']+"</option>";
      //     }
      //     $(".prod_category").empty();
      //     $(".prod_category").append(html_string);
      //   },
      //   error:function()
      //   {
      //     alert('Error');
      //   }
      // });
      // $('.prod_category').val('');
      $('.table-product').DataTable().ajax.reload();

    });

    $(document).on('change','.filter-dropdown',function(){
      $("#filter-dropdown_exp").val($('.filter-dropdown option:selected').val());
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $('#loader_modal').modal('show');
    $('.table-product').DataTable().ajax.reload();
  });

    // For deletion of incomplete products
    $(document).on('click', '.check-all', function () {
      if(this.checked == true){
        $('.check').prop('checked', true);
        $('.check').parents('tr').addClass('selected');
      }
      else{
        $('.check').prop('checked', false);
        $('.check').parents('tr').removeClass('selected');
      }
    });

    $(document).on('keyup', '.form-control', function(){
      $(this).removeClass('is-invalid');
      $(this).next().remove();
    });

  });

  $(document).on('click', '.check', function () {
    if(this.checked == true)
    {
      $('.delete-selected-item').removeClass('d-none');
      $(this).parents('tr').addClass('selected');
    }
    else
    {
      var cb_length = $( ".check:checked" ).length;
      $(this).parents('tr').removeClass('selected');
      if(cb_length == 0)
      {
        $('.delete-selected-item').addClass('d-none');
      }
    }
  });

  $(document).on("click",'.deactivate-btn',function(){
    var selected_products = [];
      $("input.check:checked").each(function() {
      selected_products.push($(this).val());
    });

      swal({
          title: "Alert!",
          text: "Are you sure you want to deactivate selected products?",
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
                data:'selected_products='+selected_products,
                url:"{{ route('deactivate-products') }}",
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
                      toastr.success('Success!', 'Selected Product(s) Deactivate Successfully.' ,{"positionClass": "toast-bottom-right"});
                      $('.table-product').DataTable().ajax.reload();
                      $('.delete-selected-item').addClass('d-none');
                      $('.check-all').prop('checked',false);
                  }
                  if(data.success == false)
                  {
                      toastr.error('Error!', data.msg ,{"positionClass": "toast-bottom-right"});
                      $('.table-product').DataTable().ajax.reload();
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

      }); //deactivate products

  $(document).on("click",'.ecommerce-products-enabled',function(){
    var selected_products = [];
      $("input.check:checked").each(function() {
      selected_products.push($(this).val());
    });

      swal({
          title: "Alert!",
          text: "Are you sure you want to enable selected products to Ecommerce?",
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
                data:'selected_products='+selected_products,
                url:"{{ route('products-enable-ecommerce') }}",
                beforeSend:function(){
                  $('#loader_modal').modal({
                      backdrop: 'static',
                      keyboard: false
                    });
                   $("#loader_modal").modal('show');
                },
                success:function(data){
                  // alert('testtest');
                  $("#loader_modal").modal('hide');
                  if(data.success == true)
                  {
                      toastr.success('Success!', 'Selected Product(s) Deactivate Successfully.' ,{"positionClass": "toast-bottom-right"});
                      $('.table-product').DataTable().ajax.reload();
                      $('.delete-selected-item').addClass('d-none');
                      $('.check-all').prop('checked',false);
                  }
                  if(data.success == false)
                  {
                      toastr.error('Error!', data.msg ,{"positionClass": "toast-bottom-right"});
                      $('.table-product').DataTable().ajax.reload();
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

      }); //Enable Over Ecommerce Product

  $(document).on("click",'.delete-btn',function(){
    var selected_products = [];
      $("input.check:checked").each(function() {
      selected_products.push($(this).val());
    });

      swal({
          title: "Alert!",
          text: "Are you sure you want to Unpublish selected products?",
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
                data:'selected_products='+selected_products,
                url:"{{ route('delete-products') }}",
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
                      toastr.success('Success!', 'Selected Product(s) Enabled for Ecommerce Successfully.' ,{"positionClass": "toast-bottom-right"});
                      $('.table-product').DataTable().ajax.reload();
                      $('.delete-selected-item').addClass('d-none');
                      $('.check-all').prop('checked',false);
                  }
                  if(data.success == false)
                  {

                      var ordered_products = "<div class='alert alert-danger alert-dismissible'><a href='#'' class='close' data-dismiss='alert' aria-label='close'>&times;</a><p class=''><strong>Note: </strong>"+data.msg+" </p></div>";

                      $('#ordered_products_alert').html(ordered_products);

                      $('.table-product').DataTable().ajax.reload();
                      $('.delete-selected-item').addClass('d-none');
                      $('.check-all').prop('checked',false);
                  }
                  if(data.error == 1)
                  {
                    toastr.error('Error!', 'You cannot delete more than 100 products at a time.' ,{"positionClass": "toast-bottom-right"});
                      $('.table-product').DataTable().ajax.reload();
                      $('.delete-selected-item').addClass('d-none');
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

      }); //delete products

  // getting product Image
  $(document).on('click', '.show-prod-image', function(e){
    let sid = $(this).data('id');
    $.ajax({
      type: "get",
      url: "{{ route('get-prod-image') }}",
      data: 'prod_id='+sid,

      success: function(response){
        $('.fetched-images').html(response);
      }
    });
  });

  // delete Product
  $(document).on('click', '.deleteProduct', function(e){

    var id = $(this).data('id');
      swal({
        title: "Are you sure?",
        text: "You want to delete this product ?",
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
              url: "{{ route('delete-product-data') }}",
              beforeSend: function(){
                $('#loader_modal').modal({
                  backdrop: 'static',
                  keyboard: false
                });
                $('#loader_modal').modal('show');
              },
              success: function(response){
                if(response.success === true){
                  toastr.success('Success!', 'Product Deleted Successfully.',{"positionClass": "toast-bottom-right"});
                  $('.table-product').DataTable().ajax.reload();
                }
              },
              error: function(request, status, error){
                $("#loader_modal").modal('hide');
              }
            });
          }
          else {
            swal("Cancelled", "", "error");
          }
      });
    });

  // add supplier product modal from dropdown
  $(document).on('click', '#addSupplierBtnDropDown', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('add-product-suppliers-dropdown') }}",
          method: 'post',
          data: $('#addSupplierModalDropDownForm').serialize(),
          beforeSend: function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
           $("#loader_modal").modal('show');
          },
          success: function(data){
            $("#loader_modal").modal('hide');
            if(data.success === true){
              toastr.success('Success!', 'Product supplier added successfully',{"positionClass": "toast-bottom-right"});
              $('.table-product').DataTable().ajax.reload();
              $('#addSupplierModalDropDownForm')[0].reset();
              $('.addSupplierModalDropDown').modal('hide');
            }


          },
          error: function (request, status, error) {
                $('.save-prod-sup-drop-down').val('add');
                $('.save-prod-sup-drop-down').removeClass('disabled');
                $('.save-prod-sup-drop-down').removeAttr('disabled');
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

  // to make fields double click editable
  $(document).on("dblclick",".inputDoubleClick",function(){
    // alert($(this).data('id'));
    var str = $(this).data('id');
    if(str !== undefined){
    var res = str.split(" ");
    }
    else{
      var res = null;
    }
   if(res !== null){
    $.ajax({
      type: "get",
      url: "{{ route('get-prod-dropdowns') }}",
      data: 'value='+res[1]+'&choice='+res[0],
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      success: function(response)
      {
        $("#loader_modal").modal('hide');
        if(response.field == 'type'){
          $('.type_select'+res[2]).append(response.html);
        }

        else if(response.field == 'unit'){
          $('.buying_select'+res[2]).append(response.html);
        }
        else if(response.field == 'selling_unit'){
          $('.selling_unit'+res[2]).append(response.html);
        }
         else if(response.field == 'category_id'){
          $('.categories_select'+res[2]).append(response.html);
        }
        // $('.fetched-images').html(response);
        // $('.product_type').empty();
        // $('.product_type').append(response.html);
          $(this).addClass('d-none');
        $(this).next().removeClass('d-none');
        $(this).next().addClass('active');
        $(this).next().focus();
        var num = $(this).next().val();
        $(this).next().focus().val('').val(num);
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });
  }
  });

  function saveProdData(prod_detail_id,field_name,field_value,old_value){
      console.log(field_name+' '+field_value+''+prod_detail_id+' '+old_value);
      // return false;
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method: "post",
        url: "{{ url('save-prod-data-incomplete-to-complete') }}",
        dataType: 'json',
        data: 'prod_detail_id='+prod_detail_id+'&'+field_name+'='+field_value+'&'+'old_value'+'='+old_value,
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
          $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
          if(data.error == 1)
          {
            toastr.error('Error!', 'Product Description already exist.',{"positionClass": "toast-bottom-right"});
          }
          if(data.dont_run == 0)
          {
            if(data.completed == 1)
            {
              toastr.success('Success!', 'Information updated successfully. Product marked as completed.',{"positionClass": "toast-bottom-right"});
            }
            else
            {
              toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
            }
          }

          if(data.reload == 1)
          {
            $('.table-product').DataTable().ajax.reload();
          }
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
    }

  // uploading shahskayssss
  // uploadHBR.init({
  //     "target": "#uploads",
  //     "max": 4,
  //     "textNew": "ADD",
  //     "textTitle": "Click here or drag to upload an image",
  //     "textTitleRemove": "Click here to remove the image"
  //   });

  // $('#reset').click(function () {
  //   uploadHBR.reset('#uploads');
  // });

  $(document).on('click', '.img-uploader', function(){
    var count = $('#images_count').val();
    var pId = $(this).parents('tr').attr('id');
    $('.img-product-id').val(pId);
  });

  // adding product images
  $('.add-prodimage-form').on('submit', function(e){
  e.preventDefault();
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    }
  });
  $.ajax({
      url: "{{ route('add-product-image') }}",
      dataType: 'json',
      type: 'post',
      data: new FormData(this),
      contentType: false,
      cache: false,
      processData:false,
      beforeSend: function(){
        $('.save-btn').html('Please wait...');
        $('.save-btn').addClass('disabled');
        $('.save-btn').attr('disabled', true);
        // $('#loader_modal').modal({
        //   backdrop: 'static',
        //   keyboard: false
        // });
        // $('#loader_modal').modal('show');
      },
      success: function(result){
        $('.save-btn').html('Upload');
        $('.save-btn').attr('disabled', true);
        $('.save-btn').removeAttr('disabled');
        // $('#loader_modal').modal('hide');
        if(result.success == true){
          toastr.success('Success!', 'Image(s) added successfully',{"positionClass": "toast-bottom-right"});
          // uploadHBR.reset('#uploads');
          $('#productImagesModal').modal('hide');
          $('.table-product').DataTable().ajax.reload();

        }else{
          toastr.error('Error!', result.errormsg,{"positionClass": "toast-bottom-right"});
          // uploadHBR.reset('#uploads');
          $('#productImagesModal').modal('hide');
        }
      },
      error: function (request, status, error) {
          // $('#loader_modal').modal('hide');
          $('.save-btn').html('Upload');
          $('.save-btn').removeClass('disabled');
          $('.save-btn').removeAttr('disabled');
          json = $.parseJSON(request.responseText);
          $.each(json.errors, function(key, value){
            $('input[name="'+key+'[]"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
            $('input[name="'+key+'[]"]').addClass('is-invalid');
          });
        }
    });
    });

  // delete image
  $(document).on('click', '.delete-img-btn', function(e){
      var id = $(this).data('img');
      var prodid = $(this).data('prodId');
      swal({
          title: "Alert!",
          text: "Are you sure you want to delete this image?",
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
                data:'id='+id+'&prodid='+prodid,
                url:"{{ route('remove-prod-image') }}",
                beforeSend:function(){
                  $('#loader_modal').modal({
                    backdrop: 'static',
                    keyboard: false
                  });
                  $("#loader_modal").modal('show');
                },
                success:function(data){
                  // $("#loader_modal").modal('hide');
                  if(data.search('done') !== -1){
                    myArray = new Array();
                    myArray = data.split('-SEPARATOR-');
                    let i_id = myArray[1];
                    $('#prod-image-'+i_id).remove();
                    toastr.success('Success!', 'Image deleted successfully.' ,{"positionClass": "toast-bottom-right"});
                  }
                },
                error: function(request, status, error){
                  $("#loader_modal").modal('hide');
                }
             });
          }
          else{
              swal("Cancelled", "", "error");
          }
     });
  });

  // Getting product category k child
  $(document).on('change', '.prod-category', function(e){
      var p_cat_id = $(this).val();
      $.ajax({
        method: "get",
        url: "{{ url('getting-product-category-childs') }}",
        dataType: 'json',
        context: this,
        data: {p_cat_id:p_cat_id},
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
          if(data.html_string)
          {
            $(this).closest('td').next('td').find('span').addClass('d-none');
            $(this).closest('td').next('td').find('span').next().removeClass('d-none');
            $(this).closest('td').next('td').find('span').next().focus();
            $(this).closest('td').next('td').find('span').next().html(data.html_string);
          }
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
  });

  // adding product on add product button on click function
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
          context: this,
          processData:false,
          beforeSend: function(){
            $('.save-prod-btn').val('Please wait...');
            $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
              });
             $("#loader_modal").modal('show');
          },
          success: function(result){
            $("#loader_modal").modal('hide');
            $('.save-prod-btn').val('Add Product');
            if(result.success === true){
              $('.modal').modal('hide');
              toastr.success('Success!', 'Product added successfully',{"positionClass": "toast-bottom-right"});
              $('.add-product-div').addClass('d-none');
              $('.product_category_dd').val("").change();
              $('.table-product').DataTable().ajax.reload();
            }
            else if(result.success === false)
            {
              $('.modal').modal('hide');
              toastr.success('Success!', 'Product added successfully',{"positionClass": "toast-bottom-right"});
              $('.add-product-div').addClass('d-none');
              $('.product_category_dd').val("").change();
              $('.table-product').DataTable().ajax.reload();

            }


          },
          error: function (request, status, error) {
                $('.save-prod-btn').val('Add Product');
                $('.save-prod-btn').removeClass('disabled');
                $('.save-prod-btn').removeAttr('disabled');
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


</script>
<script type="text/javascript">
  $(document).on('click','.upload-excel-btn',function(){
      $('#uploadExcel').modal('show');
  });



  $(document).on('keyup', function(e) {
    if (e.keyCode === 27){ // esc
      if($('.selectDoubleClick').hasClass('d-none')){
        $('.selectDoubleClick').removeClass('d-none');
        $('.selectDoubleClick').next().addClass('d-none');
      }
      if($('.inputDoubleClick').hasClass('d-none')){
        $('.inputDoubleClick').removeClass('d-none');
        $('.inputDoubleClick').next().addClass('d-none');
      }
    }
  });

  $('.price-check-btn').on('click',function(){
    $.ajax({
      method: "get",
      url: "{{ url('getting-product-incorrect-prices') }}",
      dataType: 'json',
      context: this,
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
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });
  });

  $('.update-billed-btn').on('click',function(){
    $.ajax({
      method: "get",
      url: "{{ url('update-billed-qty-script') }}",
      dataType: 'json',
      context: this,
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
        toastr.success('Success!', 'Script runs successfully',{"positionClass": "toast-bottom-right"});
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });
  });


  $('.reset-btn').on('click',function(){
      $('.default_supplier').val('');
      $('.prod_category').val('');
      $('.prod_type').val('');
      $('.prod_category_primary').val('');
      $('.filter-dropdown').val('');
      $('input[type=search]').val('');
      $(".state-tags").select2("", "");
      setTimeout(function(){
        $('.table-product').DataTable().search( " " ).draw();
       }, 20);
    });
  // $('.export-btn').on('click',function(){
  //   $("#export_complete_products_form").submit();
  // });
</script>
  <input type="hidden" name="default_supplier_exp" id="default_supplier_exp">
  <input type="hidden" name="prod_category_primary_exp" id="prod_category_primary_exp">
  <input type="hidden" name="prod_category_exp" id="prod_category_exp">
  <input type="hidden" name="prod_type_exp" id="prod_type_exp">
  <input type="hidden" name="filter-dropdown_exp" id="filter-dropdown_exp">
  <input type="hidden" name="type" id="type" value=1>
  <input type="hidden" name="search_value" id="search_value">
<script type="text/javascript">


            $(document).on('click','.download-btn',function(){
              $('.export-alert-success').addClass('d-none');
            });
            $(document).on('click','.file-not-exist-btn',function(){
              swal("Oppsss!", "File doesn't exist!", "error");
            })
</script>
@stop
