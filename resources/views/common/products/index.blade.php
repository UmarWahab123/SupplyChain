@extends($layout.'.layouts.layout')


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


.h-100{
  height: 145px !important;
}
</style>

{{-- Content Start from here --}}

<div class="row entriestable-row">
  <!-- Header is here -->

  <div class="col-lg-12 pl-0 pr-0 d-flex align-items-center mb-3">  
    <div class="col-lg-7">
      <h4>COMPLETE PRODUCTS</h4>
    </div>
   
    <div class="col-lg-3 mt-md-2">
      <select class="font-weight-bold form-control-lg form-control js-states state-tags default_supplier" name="default_supplier">
        <option value="" disabled="" selected="">Choose @if(!array_key_exists('supplier', $global_terminologies)) Supplier @else {{$global_terminologies['supplier']}} @endif</option>
        @foreach($suppliers as $supplier)
          @if(session('id'))
          <option value="{{$supplier->id}}">{{$supplier->company}}</option>
          @else
          <option value="{{$supplier->id}}">{{$supplier->company}}</option>
          @endif
        @endforeach
      </select>
    </div>

    <div class="col-lg-2">
      <input type="button" value="Reset" class="btn recived-button reset-btn">
    </div>
  </div>

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
                <!-- <th class="noVis">
                  <div class="custom-control custom-checkbox d-inline-block">
                    <input type="checkbox" class="custom-control-input check-all" name="check_all" id="check-all">
                    <label class="custom-control-label" for="check-all"></label>
                  </div>
                </th> -->
                <th>Action</th>
                <th>{{$global_terminologies['our_reference_number']}}</th>
                <th>HS Code</th>
                {{--<th>Name</th>--}}
                <th>{{$global_terminologies['category']}}</th>

                <th>{{$global_terminologies['subcategory']}}</th>
                <th width="10%">{{$global_terminologies['product_description']}}</th>
                <th>Picture</th>
                <th>Billed <br> Unit</th>
                <th>Selling <br> Unit</th>
                <th>{{$global_terminologies['type']}}</th>
                <th>{{$global_terminologies['brand']}}</th>


                <th> {{$global_terminologies['temprature_c']}}</th>
                {{--<th>Import <br> Tax<br>(Book) %</th>--}}
                {{--<th>Import <br> Tax(Actual) %</th>--}}
                <th>VAT</th>
                <th>Default/Last <br> Supplier</th>                  
                {{--<th>Buying <br> Price</th>--}}
                {{--<th>Freight</th>--}}
                {{--<th>Landing</th>--}}
                <th>{{$global_terminologies['cost_price']}}</th>
                {{--<th>Unit Conversion <br> Rate</th>--}}
                {{--<th>Selling Unit <br> Cost Price</th>--}}
                <th>Bangkok <br> Current {{$global_terminologies['qty']]}}</th>
                <th>Bangkok <br> Reserved {{$global_terminologies['qty']}}</th>
                <th>Phuket <br> Current {{$global_terminologies['qty']}}</th>
                <th>Phuket <br> Reserved {{$global_terminologies['qty']}}</th>
                {{--<th>Avg.Unit/Price</th>--}}
                <th>{{$global_terminologies['avg_units_for-sales']}}</th>
                {{--<th>Last Supplier</th>--}}
                <th>{{$global_terminologies['expected_lead_time_in_days']}}</th>                      
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
          <label class="pull-left">{{$global_terminologies['suppliers_product_reference_no']}}<b style="color: red;">*</b></label>
          <input class="font-weight-bold form-control-lg form-control" placeholder="Product Supplier Ref#." name="product_supplier_reference_no" type="text">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-6">
          <label class="pull-left">Import Tax Actual </label>
          <input class="font-weight-bold form-control-lg form-control" placeholder="Import Tax Actual" name="import_tax_actual" type="number">
        </div>

        <div class="form-group col-6">
          <label class="pull-left">{{$global_terminologies['gross_weight']}}<b style="color: red;">*</b></label>
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
          <input class="font-weight-bold form-control-lg form-control" placeholder="Buying price" name="buying_price" type="number">
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
  $(function(e){
    $(".state-tags").select2();
    var table2 = $('.table-product').DataTable({
        searching:true,
         processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        colReorder: {
            realtime: false
        },
        ordering: false,
        retrieve: true,
        scrollX: true,
        scrollY : '90vh',
    scrollCollapse: true,
        language: {
          info: "Showing  results _START_ to _END_",
        },
        columnDefs: [
            { targets: [{{ @$hidden_by_default1 }}], 
              visible: false
            },
            { className: "dt-body-left", "targets": [ 1,2,3,4,5,6,7,8,9,10,11,12,13,19,15,16,17,18,19,20] },
    { className: "dt-body-right", "targets": [ 14 ] }
        ],
        serverSide: true,
        fixedHeader: true,
        dom: 'Blfrtip',
        pageLength: {{100}},
        lengthMenu: [ 100, 200, 300, 400],
        buttons: [
            {
                extend: 'colvis',
                columns: ':not(.noVis)',
            }
        ],
        ajax: {
        url:"{!! route('common-product-list-data') !!}",
        data: function(data) { data.default_supplier = $('.default_supplier option:selected').val() } ,
         },
        
        columns: [
            // { data: 'checkbox', name: 'checkbox' },
            { data: 'action', name: 'action' },
            { data: 'refrence_code', name: 'refrence_code' },
            { data: 'hs_code', name: 'hs_code' },
            // { data: 'name', name: 'name' },
            { data: 'primary_category', name: 'primary_category' },
            { data: 'category_id', name: 'category_id' },
            { data: 'short_desc', name: 'short_desc' },
            { data: 'image', name: 'image' },
            { data: 'buying_unit', name: 'buying_unit' },
            { data: 'selling_unit', name: 'selling_unit' },
            // new added
            { data: 'product_type', name: 'product_type' },
            { data: 'product_brand', name: 'product_brand' },
            { data: 'product_temprature_c', name: 'product_temprature_c' },

            // { data: 'import_tax_book', name: 'import_tax_book' },
            // { data: 'import_tax_actual', name: 'import_tax_actual' },
            { data: 'vat', name: 'vat' },
            { data: 'supplier_id', name: 'supplier_id' },
            // { data: 'vendor_price', name: 'vendor_price' },
            // { data: 'freight', name: 'freight' },
            // { data: 'landing', name: 'landing' },
            { data: 'total_buy_unit_cost_price', name: 'total_buy_unit_cost_price' },
            // { data: 'unit_conversion_rate', name: 'unit_conversion_rate' },
            // { data: 'selling_unit_cost_price', name: 'selling_unit_cost_price' },
            { data: 'bangkok_current_qty', name: 'bangkok_current_qty' },
            { data: 'bangkok_reserved_qty', name: 'bangkok_reserved_qty' },
            { data: 'phuket_current_qty', name: 'phuket_current_qty' },
            { data: 'phuket_reserved_qty', name: 'phuket_reserved_qty' },
            // { data: 'average_unit_price', name: 'average_unit_price' },
            { data: 'weight', name: 'weight' },
            // { data: 'last_supplier', name: 'last_supplier' },
            { data: 'lead_time', name: 'lead_time' }, 
        ],
        initComplete: function () {
          // Enable THEAD scroll bars
            $('.dataTables_scrollHead').css('overflow', 'auto');
            $('.dataTables_scrollHead').on('scroll', function () {
            $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
          }); 
        }
    });

    table2.on( 'column-reorder', function ( e, settings, details ) {
        table2.button(0).remove();
        table2.button().add(0,{
          extend: 'colvis',
          autoClose: false,
          fade: 0,
          columns: ':not(.noVis)',
          colVis: { showAll: "Show all" }
        });
        table2.ajax.reload();
        var headerCell = $( table2.column( details.to ).header() );
       //console.log(details);
        headerCell.addClass( 'reordered' );
     
    });

   
});

  // getting product Image
  $(document).on('click', '.show-prod-image', function(e){
    let sid = $(this).data('id');
    $.ajax({
      type: "get",
      url: "{{ route('get-common-product-images') }}",
      data: 'prod_id='+sid,
      
      success: function(response){
        $('.fetched-images').html(response);
      }
    });
  });

  $(document).on('change','.default_supplier',function(){
    var selected = $(this).val();
    if($('.default_supplier option:selected').val() != '')
    {
      $('.table-product').DataTable().ajax.reload();
    }
  });

  $('.reset-btn').on('click',function(){
    $('.default_supplier').val('').change();
    $('.table-product').DataTable().ajax.reload();
  });
</script>


@stop