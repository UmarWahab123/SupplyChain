@extends('sales.layouts.layout')

@section('title','Customer Management | Supply Chain')

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

  <!-- header starts -->
  <div class="row d-flex align-items-center left-right-padding mb-2">
    <div class="col-lg-7">
      <h3>{{@$customer->company}} Products Fixed Prices</h3>
    </div>
  </div>

  <!-- header ends -->
<div class="row entriestable-row">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
      <table class="table headings-color const-font" style="width:100%">
    <thead class="sales-coordinator-thead">                                                     
      <tr>
        <th>Product #</th>
        <th>Default Price</th>
        <th>Fixed Price</th>
        <th>Expiration Date</th>                                                
      </tr>
    </thead>

    <tbody class="dot-dash">
    @if($ProductCustomerFixedPrice->count() > 0)
    @foreach($ProductCustomerFixedPrice as $item)
      <tr id="cust-fixed-price-{{$item->id}}">
        <td>{{@$item->products->refrence_code}}</td>
        <td>{{@$item->products->selling_price}}</td>
        <td>
      
        <span class="m-l-15 selectDoubleClick" id="fixed-price"  data-fieldvalue="{{@$item->fixed_price}}"> 
              {{(@$item->fixed_price!=null)?@$item->fixed_price:'N.A'}}
        </span>
        <input type="number" name="fixed-price" class="productFixed d-none" data-id="{{@$item->products->id}}" value="{{(@$item->fixed_price!=null)?$item->fixed_price:''}}">
        </td>
        <td>
        <span class="m-l-15 selectDoubleClick" id="expiration-date"  data-fieldvalue="{{@$item->expiration_date}}"> 
              {{(@$item->expiration_date!=null)?@$item->expiration_date:'N.A'}}
        </span>
        <input type="date" name="expiration-date" class="productFixed d-none" data-id="{{@$item->products->id}}" value="{{(@$item->expiration_date!=null)?$item->expiration_date:''}}">
        </td>
      </tr>               
    @endforeach
    @else
      <tr>
        <td colspan="5">No Fixed product Info Found</td>
      </tr>          
    @endif
    </tbody>
  </table>
    </div>
  </div>
</div>

<!-- </div> -->
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

 <!-- main content end here -->
</div><!-- main content end here -->

@endsection

@section('javascript')
<script type="text/javascript">
$(document).on("dblclick",".selectDoubleClick",function(){
      $(this).addClass('d-none');
      $(this).next().removeClass('d-none');
      $(this).next().focus();
  });

// to make that field on its orignal state
$(document).on("focusout",".productFixed",function() { 
      var id = $(this).data('id');
      // alert(id);
      var thisPointer = $(this);
      var oldValue = $(this).data('fieldvalue');
      thisPointer.addClass('d-none');
      thisPointer.prev().removeClass('d-none');
      saveProductFixedData(thisPointer,thisPointer.attr('name'), thisPointer.val(),id);
  });

function saveProductFixedData(thisPointer,field_name,field_value,id){
    console.log(thisPointer);
    console.log(field_name);
    console.log(field_value);
    console.log(id);
    var cust_detail_id= "{{$customer->id}}";
    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });

      $.ajax({
        method: "post",
        url: "{{ url('sales/save-product-update-record') }}",
        dataType: 'json',
        // data: {shipping_id:shipping_id,cust_detail_id:cust_detail_id},
        data: {field_name:field_name,field_value:field_value,cust_detail_id:cust_detail_id,product_id:id},

        beforeSend: function(){
          // shahsky here
        },
        success: function(data)
        {
          console.log(data);
         if(field_name == "fixed-price")
          {
           if(data.error == false)
           {
              $("#fixed-price").html(data.product.fixed_price); 
              $('input[name=fixed-price]').val(data.product.fixed_price);

              toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
              return false;
            } 
          } 
         else if(field_name == "expiration-date")
          {
           if(data.error == false)
           {
              $("#expiration-date").html(data.product.expiration_date); 
              $('input[name=expiration-date]').val(data.product.expiration_date);

              toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
              return false;
            } 
          } 

          else if(field_name == "city")
          {
           if(data.error == false)
           {
              $("#shipping-city").html(data.customerShipping.shipping_city); 
              $('input[name=city]').val(data.customerShipping.shipping_city);

              toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
              return false;
            } 
          } 
          
        },

      });
  }
</script>
@stop

