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
p
{
  font-size: small;
  font-style: italic;
  color: red;
}
.h-100{
  height: 145px !important;
}
.selectDoubleClick, .inputDoubleClick{
    font-style: italic;
}
.select2-container{
  width: 100% !important;
}
.select2-dropdown{
  width: 260px !important;
}
/*.select2-container.select2-dropdown-open {
    width: 170% !important;
  }*/
</style>

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
          <li class="breadcrumb-item active">Incomplete Products</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}


  <!-- Header is here -->

  <div class="row align-items-center mb-3">
    <div class="col-lg-8 col-md-7 title-col">
      <h4 class="maintitle incomplete-products-heading">INCOMPLETE PRODUCTS <p class=
        "incomplete-products-paragraph" style="margin-top: 5px;">Note: The ITALIC text is double click editable.</p></h4>
    </div>
    <div class="col-lg-4 col-md-5 d-flex incomplete-filter ml-md-auto filters_div">
      <select class="font-weight-bold form-control-lg form-control js-states state-tags product_category_dd" name="category" required="true">
        <option value="0" disabled="" selected="">Choose @if(!array_key_exists('category', $global_terminologies)) Category @else {{$global_terminologies['category']}} @endif</option>
        @if($parentCat)
        @foreach($parentCat as $pcat)
        <optgroup label="{{$pcat->title}}">
          @php
            $subCat = App\Models\Common\ProductCategory::where('parent_id',$pcat->id)->orderBy('title')->get();
          @endphp
          @foreach($subCat as $scat)
          <option value="{{$scat->id}}">{{$scat->title}}</option>
          @endforeach
        </optgroup>
        @endforeach
        @endif
      </select>

      <div class="input-group-append ml-3 ">
      <!-- <button class="btn recived-button" type="submit">Add Product</button>   -->
      {!! Form::open(['method' => 'POST', 'id' => 'save_prod_btn' ,'class' => 'add-product-form', 'enctype' => 'multipart/form-data']) !!}
      <input type="hidden" name="selected_category_id" id="selected_category_id" value="">
      <div class="mb-0">
        <input type="submit" value="Add Product" class="btn recived-button save-prod-btn">
      </div>
      {!! Form::close() !!}
      </div>
        <div class="col-xl-2 col-lg-2 col-md-3 p-0">
        <div class="input-group-append" style="padding-left: 10px;">
        <span class="common-icons reset-btn" id="reset-btn" title="Reset">
          <img src="{{asset('public/icons/reset.png')}}" width="27px">
        </span>
        </div>
      </div>
    </div>
  </div>
<div class="row entriestable-row">
  <!-- End -->
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
    <div class="delete-selected-item catalogue-btn-group d-none">
        @if(Auth::user()->role_id == 2)
          <a href="javascript:void(0);" class="btn selected-item-btn btn-sm deleteBtnImg delete-btn" data-toggle="tooltip" title="Delete Selected Items"><i class="fa fa-trash"></i></a>
        @endif

        </div>
        <div class="table-responsive">
          <table class="table entriestable table-bordered table-product text-center purchase-complete-product">
            <thead>
              <tr>
                <th class="noVis">
                  <div class="custom-control custom-checkbox d-inline-block">
                    <input type="checkbox" class="custom-control-input check-all" name="check_all" id="check-all">
                    <label class="custom-control-label" for="check-all"></label>
                  </div>
                </th>
                <th>Action</th>
                <th id="pf_length">{{$global_terminologies['our_reference_number']}}</th>
                <th>{{$global_terminologies['suppliers_product_reference_no']}}</th>
                <th>HS Code</th>
                {{--<th>Name</th>
                <th>Category</th>
                <th>Sub <br> Category</th>--}}
                <th>{{$global_terminologies['category']}} / {{$global_terminologies['subcategory']}}
           </th>
            <th>{{$global_terminologies['product_description']}}</th>

                <th>Picture</th>
                <th>Billed <br> Unit</th>
                <th>Selling <br> Unit</th>
                <th>{{$global_terminologies['type']}}</th>
                <th @if (!in_array('product_type_2', $product_detail_section)) class="noVis" @endif>@if(!array_key_exists('product_type_2', $global_terminologies)) Type 2 @else {{$global_terminologies['product_type_2']}} @endif</th>
                <th @if (!in_array('product_type_3', $product_detail_section)) class="noVis" @endif>@if(!array_key_exists('product_type_3', $global_terminologies)) Type 3 @else {{$global_terminologies['product_type_3']}} @endif</th>
                <th>{{$global_terminologies['brand']}}</th>

                <th>{{$global_terminologies['temprature_c']}}</th>
                <th>Import <br> Tax<br>(Book) %</th>
                {{--<th>Import <br> Tax(Actual) %</th>--}}
                <th>VAT</th>
                <th>Default/Last <br>Supplier</th>
                <th>{{$global_terminologies['supplier_description']}}</th>

                <th>{{$global_terminologies['purchasing_price']}}<br><b>(EUR)</b></th>
                <th>{{$global_terminologies['purchasing_price']}}<br><b>(THB)</b></th>
                <th>{{$global_terminologies['freight_per_billed_unit']}}</th>
                <th>{{$global_terminologies['landing_per_billed_unit']}}</th>
                <!-- <th>{{$global_terminologies['cost_price']}}</th> -->
                <th>{{$global_terminologies['cost_price']}}</th>
                <th>Restaurant Price</th>
                <th id="unit_con">{{$global_terminologies['unit_conversion_rate']}}</th>
                <th>{{$global_terminologies['net_price']}} /unit (THB)</th>
                {{--<th>Avg.Unit/Price</th>--}}
                <th id='avg_length'>{{$global_terminologies['avg_units_for-sales']}}</th>
                {{--<th>Last Supplier</th>--}}
                <th id="exp_length">{{$global_terminologies['expected_lead_time_in_days']}}</th>
              </tr>
            </thead>
          </table>
        </div>
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
    <div class="modal-dialog">
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


@endsection
  <?php
    $hidden_by_default = '';
  ?>
@section('javascript')
<script type="text/javascript">
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
  // $(".incomp-select2").select2({dropdownCssClass : 'bigdrop'});

    var table2 = $('.table-product').DataTable({
    drawCallback: function() {
       $('.state-tags').select2();
       $('.incomp-select2').select2({dropdownCssClass : 'bigdrop'});
      },
      "sPaginationType": "listbox",
        oLanguage:
        {
          sProcessing: '<img src="{{ asset('public/uploads/gif/waiting.gif') }}">'
        },
        searching:true,
        processing: false,
        "language": {
          processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        stateSave:false,
        colReorder:
        {
          realtime: false
        },
        bSort: false,
        ordering: false,
        retrieve: true,
        scrollX: true,
        scrollY : '90vh',
        scrollCollapse: true,
        info:           true,
        /*Table column to show and hide*/
        columnDefs: [
          { targets: [{{ ($table_hide_columns != null) ? $table_hide_columns->hide_columns : $hidden_by_default }}], visible: false },
          { className: "dt-body-left", "targets": [ 2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,26 ] },
          { className: "dt-body-right", "targets": [ 17,18,19,20,21,22,23,24,25 ] }
        ],
        serverSide: true,
        fixedHeader: true,
        dom: 'Blfrtip',
        pageLength: {{50}},
        lengthMenu: [ 50, 100, 150, 200],
        buttons: [
          {
            extend: 'colvis',
            columns: ':not(.noVis)',
          }
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
        url:"{!! route('get-purchase-incomplete-product') !!}",
        data: function(data) { data.product_category_dd = $('.product_category_dd option:selected').val() } ,
        method: "get",
         },

        columns: [
          { data: 'checkbox', name: 'checkbox' },
          { data: 'action', name: 'action' },
          { data: 'refrence_code', name: 'refrence_code' },
          { data: 'p_s_reference_number', name: 'p_s_reference_number' },
          { data: 'hs_code', name: 'hs_code' },
          // { data: 'name', name: 'name' },
          // { data: 'primary_category', name: 'primary_category' },
          // { data: 'category_id', name: 'category_id' },
          { data: 'category_id', name: 'category_id' },
          { data: 'short_desc', name: 'short_desc' },
          { data: 'image', name: 'image' },
          { data: 'buying_unit', name: 'buying_unit' },
          { data: 'selling_unit', name: 'selling_unit' },
          { data: 'product_type', name: 'product_type' },
          { data: 'product_type_2', name: 'product_type_2' @if (!in_array('product_type_2', $product_detail_section)) ,searchable: false, orderable: false, visible: false @endif },
          { data: 'product_type_3', name: 'product_type_3' @if (!in_array('product_type_3', $product_detail_section)) ,searchable: false, orderable: false, visible: false @endif},
          { data: 'product_brand', name: 'product_brand' },
          { data: 'product_temprature_c', name: 'product_temprature_c' },
          { data: 'import_tax_book', name: 'import_tax_book' },
          { data: 'vat', name: 'vat' },
          { data: 'supplier_id', name: 'supplier_id' },
          { data: 'supplier_desc', name: 'supplier_desc' },
          { data: 'vendor_price', name: 'vendor_price' },
          { data: 'vendor_price_in_thb', name: 'vendor_price_in_thb' },
          { data: 'freight', name: 'freight' },
          { data: 'landing', name: 'landing' },
          // { data: 't_b_u_c_p_of_supplier', name: 't_b_u_c_p_of_supplier' },
          { data: 'total_buy_unit_cost_price', name: 'total_buy_unit_cost_price' },
          { data: 'restaruant_price', name: 'restaruant_price' },
          { data: 'unit_conversion_rate', name: 'unit_conversion_rate' },
          { data: 'selling_unit_cost_price', name: 'selling_unit_cost_price' },
          { data: 'weight', name: 'weight' },
          { data: 'lead_time', name: 'lead_time' },
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
        },
        drawCallback: function(){
          $('#loader_modal').modal('hide');
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
              data : {type:'incomplete_products',column_id:col},
              beforeSend:function(){
                $('#loader_modal').modal({
                  backdrop: 'static',
                  keyboard: false
                });
                $("#loader_modal").modal('show');
              },
              success: function(data){
                $("#loader_modal").modal('hide');
                if(data.success == true){
                  toastr.success('Success!', 'Product Column hidden/visible successfully.' ,{"positionClass": "toast-bottom-right"});
                  table2.ajax.reload();

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
         data : "type=incomplete_products&order="+table2.colReorder.order(),
         beforeSend:function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal").modal('show');
         },
         success: function(data){
          $("#loader_modal").modal('hide');
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
    var selected_products = [];
      $("input.check:checked").each(function() {
      selected_products.push($(this).val());
    });

      swal({
          title: "Alert!",
          text: "Are you sure you want to delete selected products? You won't be able to undo this.",
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
                url:"{{ route('remove-multi-products') }}",
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
                      toastr.success('Success!', 'Products deleted successfully.' ,{"positionClass": "toast-bottom-right"});
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
      beforeSend:function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").modal('show');
      },
      success: function(response){
        $("#loader_modal").modal('hide');
        $('.fetched-images').html(response);
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
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
              beforeSend:function(){
                $('#loader_modal').modal({
                  backdrop: 'static',
                  keyboard: false
                });
                $("#loader_modal").modal('show');
              },
              success: function(response){
                $("#loader_modal").modal('hide');
                if(response.success === true){
                  toastr.success('Success!', 'Product Deleted Successfully.',{"positionClass": "toast-bottom-right"});
                }
                else{
                    toastr.error('Error!', 'Cannot delete. This Product is already in complete status.',{"positionClass": "toast-bottom-right"});
                }
                $('.table-product').DataTable().ajax.reload();
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

      var thisPointer= $(this);
      var pId = $(this).parents('tr').attr('id');
      var old_value = $('.inc-fil-cat').prev().data('fieldvalue');
      swal({
        title: "Are you sure?",
        text: "Are you sure you want to update the Category of this product? This will update its Reference #, and its prices",
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
        data: 'prod_detail_id='+prod_detail_id+'&'+field_name+'='+encodeURIComponent(field_value)+'&'+'old_value'+'='+encodeURIComponent(old_value),
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
          if(field_name == 'short_desc')
          {
            if(data.error ==  1)
            {
              toastr.error('Error!', 'Information not updated successfully. Product description already exist.',{"positionClass": "toast-bottom-right"});
              $('.table-product').DataTable().ajax.reload();
            }
            else
            {
              toastr.success('Success!', 'Information updated successfully. Product description Entered.',{"positionClass": "toast-bottom-right"});
            }
          }
          if(data.completed == 1)
          {
            toastr.success('Success!', 'Information updated successfully. Product marked as completed.',{"positionClass": "toast-bottom-right"});
            $('.table-product').DataTable().ajax.reload();

          }
          else
          {
            // var des = $('.testing_des').val();
            //alert(des.length);
            // $('.desc_lenght').css('width',parseInt(des.length)+'%');
            // table2.draw();
            // table2.columns.adjust().draw();
            // $('.table-product').columns.adjust().draw();
             $('.table-product').DataTable().ajax.reload();
            //toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
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



$('.table-product').on('click', '.desc-width', function(){
  var desclenght = $('.desc_lenght ').innerWidth();
  $(this).parents('td').css('min-width', (desclenght - 10));
});

$('.table-product').on('click', '.brand-width', function(){
  var brand_width = $('.brand_width').innerWidth();
  $(this).parents('td').css('min-width', (brand_width - 10));
});

$('.table-product').on('click', '.temp-width', function(){
  var temp_width = $('.temp_width').innerWidth();
  $(this).parents('td').css('min-width', (temp_width - 10));
});

$('.table-product').on('click', '.sup-width', function(){
  var temp_width = $('.supplier-width').innerWidth();
  $(this).parents('td').css('min-width', (temp_width - 10));
});

$('.table-product').on('click', '.sup_desc_width', function(){
  var temp_width = $('.sup-desc-width').innerWidth();
  $(this).parents('td').css('min-width', (temp_width - 10));
});

  // uploading shahskayssss
  uploadHBR.init({
      "target": "#uploads",
      "max": 4,
      "textNew": "ADD",
      "textTitle": "Click here or drag to upload an image",
      "textTitleRemove": "Click here to remove the image"
    });

  $('#reset').click(function () {
    uploadHBR.reset('#uploads');
  });

  $(document).on('click', '.img-uploader', function(){
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
          uploadHBR.reset('#uploads');
          $('#productImagesModal').modal('hide');
          $('.table-product').DataTable().ajax.reload();

        }else{
          toastr.error('Error!', result.errormsg,{"positionClass": "toast-bottom-right"});
          uploadHBR.reset('#uploads');
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
                  $("#loader_modal").modal('hide');
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
        beforeSend:function(){
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
      // alert("Yes");
      var product_category_val = $('.product_category_dd').val();
      if(product_category_val != null)
      {
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
            if(result.success === true)
            {
              // Do nothing here because ajax gose on false condition
            }
            else if(result.success === false)
            {
              $('.modal').modal('hide');
              toastr.success('Success!', 'Product added successfully',{"positionClass": "toast-bottom-right"});
              $(".table-product").dataTable().fnDestroy();
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
      }
      else
      {
        toastr.error('Info!', 'Select Category first' ,{"positionClass": "toast-bottom-right"});

      }
    });

</script>
<script type="text/javascript">
  $('.add-product-div').hide();

  $(document).on('click','.upload-excel-btn',function(){
      $('#uploadExcel').modal('show');
  });

  $(document).on('change','.product_category_dd',function(){

      var selected = $(this).val();
      if($('.product_category_dd option:selected').val() != '')
      {
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
        $('.table-product').DataTable().ajax.reload();
      }
      if(selected != '')
      {
        $('.add-product-div').show();
        $('#selected_category_id').val(selected);
      }
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

  $('.reset-btn').on('click',function(){
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $("#loader_modal").modal('show');
    $('.product_category_dd').val('');
    $('.product_category_dd').val('0').change();

   });

</script>

@stop
