@extends('sales.layouts.layout')

@section('title','Products Management | Sales')

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
    <h3 class="maintitle text-uppercase fontbold">Completed Products</h3>
  </div>
 
</div>


  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
    <div class="delete-selected-item catalogue-btn-group d-none">
          @if(Auth::user()->role_id == 2)
            <a href="javascript:void(0);" class="btn selected-item-btn btn-sm deleteBtnImg delete-btn" data-toggle="tooltip" title="Delete Selected Items"><i class="fa fa-trash"></i></a>
          @endif  
      
        </div>
        <div class="table-responsive">
          <table class="table entriestable table-bordered table-product text-center table-customer">
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
                <th>Buying <br> Unit</th>
                <th>Selling <br> Unit</th>
                <th>{{$global_terminologies['type']}} </th>
                <th>{{$global_terminologies['brand']}} </th>
                <th>{{$global_terminologies['temprature_c']}} </th>
                <th>Import <br> Tax<br>(Book) %</th>
                {{--<th>Import <br> Tax(Actual) %</th>--}}
                <th>VAT</th>
                <th>Default/Last <br> Supplier</th>                  
                <th>{{$global_terminologies['purchasing_price']}}</th>
                <th>{{$global_terminologies['freight_per_billed_unit']}}</th>
                <th>{{$global_terminologies['landing_per_billed_unit']}}</th>
                <th>{{$global_terminologies['cost_price']}}</th>
                <th>{{$global_terminologies['unit_conversion_rate']}}</th>
                <th>Selling Unit <br> Cost Price</th>
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
<!--  Content End Here -->

<!-- Open Quotation Modal -->
<div class="modal" id="invoiceModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Choose a Quotation</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal body -->
      <div class="modal-body curr-order-quotation">
      </div>

      <!-- Modal footer -->
      <div class="modal-footer">
        <a href="javascript:void(0);" class="btn create-new-quo" data-action="new">Create New</a>
        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

{{-- Product Images Modal --}}
<div class="modal" id="images-modal">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Product Image</h4>
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


@endsection

@section('javascript')
<script type="text/javascript">
  $(function(e){

    $(document).on("focus", ".datepicker", function(){
        $(this).datetimepicker({
            timepicker:false,
            format:'Y-m-d'});
    });
     $('.table-customer').DataTable({
      processing: true,
      "language": {
      processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      ordering: false,
      serverSide: true,
        
      lengthMenu: [ 100, 200, 300, 400],
      ajax: "{!! url('sales/get-datatables-for-products') !!}",
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

            { data: 'import_tax_book', name: 'import_tax_book' },
            // { data: 'import_tax_actual', name: 'import_tax_actual' },
            { data: 'vat', name: 'vat' },
            { data: 'supplier_id', name: 'supplier_id' },
            { data: 'vendor_price', name: 'vendor_price' },
            { data: 'freight', name: 'freight' },
            { data: 'landing', name: 'landing' },
            { data: 'total_buy_unit_cost_price', name: 'total_buy_unit_cost_price' },
            { data: 'unit_conversion_rate', name: 'unit_conversion_rate' },
            { data: 'selling_unit_cost_price', name: 'selling_unit_cost_price' },
            // { data: 'average_unit_price', name: 'average_unit_price' },
            { data: 'weight', name: 'weight' },
            // { data: 'last_supplier', name: 'last_supplier' },
            { data: 'lead_time', name: 'lead_time' }, 
              ]
    });

     // getting product Image
  $(document).on('click', '.show-prod-image', function(e){
    let sid = $(this).data('id');
    $.ajax({
      type: "get",
      url: "{{ url('sales/get-prod-image') }}",
      data: 'prod_id='+sid,
      
      success: function(response){
         $('.fetched-images').html(response);
        
      }
    });

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
        var cb_length = $( ".check:checked" ).length;
        var st_pieces = $(this).parents('tr').attr('data-pieces');
        if(this.checked == true){ 
        $('.delete-selected-item').removeClass('d-none');
        
        $(this).parents('tr').addClass('selected');
        }
        
      else{
        $(this).parents('tr').removeClass('selected');
        if(cb_length == 0){
         $('.delete-selected-item').addClass('d-none');
        }

      }
    });

    $(document).on('keyup', '.form-control', function(){
      $(this).removeClass('is-invalid');
      $(this).next().remove();
    }); 

   

  });
</script>

@stop

