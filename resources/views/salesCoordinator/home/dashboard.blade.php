@extends('salesCoordinator.layouts.layout')
@section('title','Dashboard')


@section('content')

{{-- Content Start from here --}}

<!-- Right Content Start Here -->
<div class="right-contentIn">
  <h1>Sales coordinator</h1>
  <!-- upper section start -->
<div class="row mb-3">
<!-- left Side Start -->

<div class="col-lg-12">
<!-- 1st four box row start -->
<div class="row mb-3 headings-color">

<div class="col ">
<div class="bg-white box1 pt-4 pb-4">
  <div class="d-flex align-items-center justify-content-center">
    <img src="assets/img/img1.jpg" class="img-fluid">
    <div class="title pl-2">
      <h6 class="mb-0 number-size"> &#946;5009.09</h6>
      <span>Quotations</span>
    </div>
  </div>
</div>
</div>

<div class="col ">
<div class="bg-white box2 pt-4 pb-4">
  <div class="d-flex align-items-center justify-content-center">
    <img src="assets/img/img3.jpg" class="img-fluid">
    <div class="title pl-2">
      <h6 class="mb-0 number-size">43345+</h6>
      <span>Selecting Vendor</span>
    </div>
  </div>
</div>
</div>
<!-- 
<div class="col ">
<div class="bg-white box2 pt-4 pb-4">
  <div class="d-flex align-items-center justify-content-center">
    <img src="assets/img/img2.jpg" class="img-fluid">
    <div class="title pl-2">
      <h6 class="mb-0">43345+</h6>
      <span>Clients</span>
    </div>
  </div>
</div>
</div> -->

<div class="col ">
<div class="bg-white box3 pt-4 pb-4">
  <div class="d-flex align-items-center justify-content-center">
  <img src="assets/img/img2.jpg" class="img-fluid">
    <div class="title pl-2">
      <h6 class="mb-0 number-size">43345+</h6>
      <span>Purchasing</span>
    </div>
  </div>
</div>
</div>

<div class="col ">
<div class="bg-white box4 pt-4 pb-4">
  <div class="d-flex align-items-center justify-content-center">
    <img src="assets/img/img4.jpg" class="img-fluid">
    <div class="title pl-2">
      <h6 class="mb-0 number-size">890+</h6>
      <span>Products</span>
    </div>
  </div>
</div>
</div>

<div class="col ">
<div class="bg-white box5 pt-4 pb-4">
  <div class="d-flex align-items-center justify-content-center">
    <img src="assets/img/img7.1.jpg" class="img-fluid">
    <div class="title pl-2">
      <h6 class="mb-0 number-size">1200+</h6>
      <span>Delivery</span>
    </div>
  </div>
</div>
</div>

<div class="col ">
<div class="bg-white box6 pt-4 pb-4">
  <div class="d-flex align-items-center justify-content-center">
    <img src="assets/img/img7.1.jpg" class="img-fluid">
    <div class="title pl-2">
      <h6 class="mb-0 number-size">1200+</h6>
      <span>Completed Orders</span>
    </div>
  </div>
</div>
</div>

</div>
<!-- first four box row end-->

</div>
<!-- left Side End -->
<!-- upper section end  -->
</div>

<div class="row mb-3 headings-color">

    <div class="col-lg-9">
  <h4>Sales Coordinator Dashboard</h4>
</div>
<div class="col-lg-3">
   <div class="form-group">
                <select class="form-control">
                    <option>Quotations</option>
                    <option>1</option>
                    <option>2</option>
                    <option>3</option>
                </select>
                </div>
</div>

<div class="col-lg-12">
  <div class="row">
    <div class="col">
       <div class="form-group">
      <input type="text" class="form-control" placeholder="ID/Customer Name" name="text2">
    </div>
    </div>

    <div class="col">
      <div class="form-group">
      <select class="form-control">
        <option>From Date</option>
        <option>1</option>
        <option>2</option>
      </select>
    </div>
  </div>

  <div class="col">
      <div class="form-group">
      <select class="form-control">
        <option>To Date</option>
        <option>1</option>
        <option>2</option>
      </select>
    </div>
  </div>

  <div class="col">
      <div class="form-group">
      <select class="form-control">
        <option>Status</option>
        <option>1</option>
        <option>2</option>
      </select>
    </div>
  </div>

  <div class="col">
      <div class="form-group">
      <select class="form-control">
        <option>Type</option>
        <option>1</option>
        <option>2</option>
      </select>
    </div>
  </div>

    <div class="col">
       <div class="form-group">
      <input type="text" class="form-control" placeholder="Product Assignment" name="text2">
    </div>
    </div>

    <div class="col">
      <div class="input-group-append ml-3">
      <button class="btn recived-button" type="submit">Search</button>  
     </div>
    </div>


</div>
</div>


 <div class="col-lg-12">
<div class="bg-white">
  <table id="example" class="table headings-color" style="width:100%">
        <thead class="sales-coordinator-thead">
            <tr>
              <th>
                <div class="custom-control custom-checkbox">
      <input type="checkbox" class="custom-control-input" id="sale-cor-dash1" name="example1">
      <label class="custom-control-label" for="sale-cor-dash1"></label>
    </div>
  </th>                           
                 <th>Order #</th>
                <th>Company</th>
                <th>Customer #</th>
                <th>Date Purchased </th>
                <th>Order Total </th>
                <th>@if(!array_key_exists('target_ship_date', $global_terminologies)) Target Ship Date @else {{$global_terminologies['target_ship_date']}} @endif</th>
                <th>Note</th>
                <th>Status</th>
                                                                 
            </tr>
        </thead>
        <tbody class="dot-dash">
            <tr>
                <td><div class="custom-control custom-checkbox">
      <input type="checkbox" class="custom-control-input" id="sale-cor-dash2" name="example1">
      <label class="custom-control-label" for="sale-cor-dash2"></label>
    </div></td>
                <td>#1</td>
                <td>Gleichner, Ziemann</td>
                <td>12345</td>
                <td>14/09/2019</td>
                <td>$ 12,235</td>
                <td>14/09/2019</td>
                <td>--</td>
               <td><span class="sentverification">Sent. Waiting Confirm</span></td>
            </tr>

            <tr>
                <td><div class="custom-control custom-checkbox">
      <input type="checkbox" class="custom-control-input" id="sale-cor-dash3" name="example1">
      <label class="custom-control-label" for="sale-cor-dash3"></label>
    </div></td>
                <td>#1</td>
                <td>Gleichner, Ziemann</td>
                <td>12345</td>
                <td>14/09/2019</td>
                <td>$ 12,235</td>
                <td>14/09/2019</td>
                <td>--</td>
               <td><span class="sentverification">Sent. Waiting Confirm</span></td>
            </tr>

            <tr>
                <td><div class="custom-control custom-checkbox">
      <input type="checkbox" class="custom-control-input" id="sale-cor-dash4" name="example1">
      <label class="custom-control-label" for="sale-cor-dash4"></label>
    </div></td>
                <td>#1</td>
                <td>Gleichner, Ziemann</td>
                <td>12345</td>
                <td>14/09/2019</td>
                <td>$ 12,235</td>
                <td>14/09/2019</td>
                <td>--</td>
               <td><span class="sentverification">Sent. Waiting Confirm</span></td>
            </tr>

            <tr>
                <td><div class="custom-control custom-checkbox">
      <input type="checkbox" class="custom-control-input" id="sale-cor-dash5" name="example1">
      <label class="custom-control-label" for="sale-cor-dash5"></label>
    </div></td>
                <td>#1</td>
                <td>Gleichner, Ziemann</td>
                <td>12345</td>
                <td>14/09/2019</td>
                <td>$ 12,235</td>
                <td>14/09/2019</td>
                <td>--</td>
               <td><span class="sentverification">Sent. Waiting Confirm</span></td>
            </tr>

            <tr>
                <td><div class="custom-control custom-checkbox">
      <input type="checkbox" class="custom-control-input" id="sale-cor-dash6" name="example1">
      <label class="custom-control-label" for="sale-cor-dash6"></label>
    </div></td>
                <td>#1</td>
                <td>Gleichner, Ziemann</td>
                <td>12345</td>
                <td>14/09/2019</td>
                <td>$ 12,235</td>
                <td>14/09/2019</td>
                <td>--</td>
               <td><span class="sentverification">Sent. Waiting Confirm</span></td>
            </tr>

            <tr>
                <td><div class="custom-control custom-checkbox">
      <input type="checkbox" class="custom-control-input" id="sale-cor-dash7" name="example1">
      <label class="custom-control-label" for="sale-cor-dash7"></label>
    </div></td>
                <td>#1</td>
                <td>Gleichner, Ziemann</td>
                <td>12345</td>
                <td>14/09/2019</td>
                <td>$ 12,235</td>
                <td>14/09/2019</td>
                <td>--</td>
               <td><span class="sentverification">Sent. Waiting Confirm</span></td>
            </tr>

            <tr>
                <td><div class="custom-control custom-checkbox">
      <input type="checkbox" class="custom-control-input" id="sale-cor-dash8" name="example1">
      <label class="custom-control-label" for="sale-cor-dash8"></label>
    </div></td>
                <td>#1</td>
                <td>Gleichner, Ziemann</td>
                <td>12345</td>
                <td>14/09/2019</td>
                <td>$ 12,235</td>
                <td>14/09/2019</td>
                <td>--</td>
               <td><span class="sentverification">Sent. Waiting Confirm</span></td>
            </tr>

            <tr>
                <td><div class="custom-control custom-checkbox">
      <input type="checkbox" class="custom-control-input" id="sale-cor-dash9" name="example1">
      <label class="custom-control-label" for="sale-cor-dash9"></label>
    </div></td>
                <td>#1</td>
                <td>Gleichner, Ziemann</td>
                <td>12345</td>
                <td>14/09/2019</td>
                <td>$ 12,235</td>
                <td>14/09/2019</td>
                <td>--</td>
               <td><span class="sentverification">Sent. Waiting Confirm</span></td>
            </tr>

            <tr>
                <td><div class="custom-control custom-checkbox">
      <input type="checkbox" class="custom-control-input" id="sale-cor-dash10" name="example1">
      <label class="custom-control-label" for="sale-cor-dash10"></label>
    </div></td>
                <td>#1</td>
                <td>Gleichner, Ziemann</td>
                <td>12345</td>
                <td>14/09/2019</td>
                <td>$ 12,235</td>
                <td>14/09/2019</td>
                <td>--</td>
               <td><span class="sentverification">Sent. Waiting Confirm</span></td>
            </tr>

            <tr>
                <td><div class="custom-control custom-checkbox">
      <input type="checkbox" class="custom-control-input" id="sale-cor-dash11" name="example1">
      <label class="custom-control-label" for="sale-cor-dash11"></label>
    </div></td>
                <td>#1</td>
                <td>Gleichner, Ziemann</td>
                <td>12345</td>
                <td>14/09/2019</td>
                <td>$ 12,235</td>
                <td>14/09/2019</td>
                <td>--</td>
               <td><span class="sentverification">Sent. Waiting Confirm</span></td>
            </tr>

            <tr>
                <td><div class="custom-control custom-checkbox">
      <input type="checkbox" class="custom-control-input" id="sale-cor-dash12" name="example1">
      <label class="custom-control-label" for="sale-cor-dash12"></label>
    </div></td>
                <td>#1</td>
                <td>Gleichner, Ziemann</td>
                <td>12345</td>
                <td>14/09/2019</td>
                <td>$ 12,235</td>
                <td>14/09/2019</td>
                <td>--</td>
               <td><span class="sentverification">Sent. Waiting Confirm</span></td>
            </tr>

            <tr>
                <td><div class="custom-control custom-checkbox">
      <input type="checkbox" class="custom-control-input" id="sale-cor-dash13" name="example1">
      <label class="custom-control-label" for="sale-cor-dash13"></label>
    </div></td>
                <td>#1</td>
                <td>Gleichner, Ziemann</td>
                <td>12345</td>
                <td>14/09/2019</td>
                <td>$ 12,235</td>
                <td>14/09/2019</td>
                <td>--</td>
               <td><span class="sentverification">Sent. Waiting Confirm</span></td>
            </tr>

            <tr>
                <td><div class="custom-control custom-checkbox">
      <input type="checkbox" class="custom-control-input" id="sale-cor-dash14" name="example1">
      <label class="custom-control-label" for="sale-cor-dash14"></label>
    </div></td>
                <td>#1</td>
                <td>Gleichner, Ziemann</td>
                <td>12345</td>
                <td>14/09/2019</td>
                <td>$ 12,235</td>
                <td>14/09/2019</td>
                <td>--</td>
               <td><span class="sentverification">Sent. Waiting Confirm</span></td>
            </tr>

            <tr>
                <td><div class="custom-control custom-checkbox">
      <input type="checkbox" class="custom-control-input" id="sale-cor-dash15" name="example1">
      <label class="custom-control-label" for="sale-cor-dash15"></label>
    </div></td>
                <td>#1</td>
                <td>Gleichner, Ziemann</td>
                <td>12345</td>
                <td>14/09/2019</td>
                <td>$ 12,235</td>
                <td>14/09/2019</td>
                <td>--</td>
               <td><span class="sentverification">Sent. Waiting Confirm</span></td>
            </tr>

            <tr>
                <td><div class="custom-control custom-checkbox">
      <input type="checkbox" class="custom-control-input" id="sale-cor-dash16" name="example1">
      <label class="custom-control-label" for="sale-cor-dash16"></label>
    </div></td>
                <td>#1</td>
                <td>Gleichner, Ziemann</td>
                <td>12345</td>
                <td>14/09/2019</td>
                <td>$ 12,235</td>
                <td>14/09/2019</td>
                <td>--</td>
               <td><span class="sentverification">Sent. Waiting Confirm</span></td>
            </tr>

            <tr>
                <td><div class="custom-control custom-checkbox">
      <input type="checkbox" class="custom-control-input" id="sale-cor-dash17" name="example1">
      <label class="custom-control-label" for="sale-cor-dash17"></label>
    </div></td>
                <td>#1</td>
                <td>Gleichner, Ziemann</td>
                <td>12345</td>
                <td>14/09/2019</td>
                <td>$ 12,235</td>
                <td>14/09/2019</td>
                <td>--</td>
               <td><span class="sentverification">Sent. Waiting Confirm</span></td>
            </tr>

            <tr>
                <td><div class="custom-control custom-checkbox">
      <input type="checkbox" class="custom-control-input" id="sale-cor-dash18" name="example1">
      <label class="custom-control-label" for="sale-cor-dash18"></label>
    </div></td>
                <td>#1</td>
                <td>Gleichner, Ziemann</td>
                <td>12345</td>
                <td>14/09/2019</td>
                <td>$ 12,235</td>
                <td>14/09/2019</td>
                <td>--</td>
               <td><span class="sentverification">Sent. Waiting Confirm</span></td>
            </tr>

          
                       
           
        </tbody>
    </table>
</div>


</div>

</div>
</div>

@endsection


@section('javascript')
<script type="text/javascript">
  $(function(e){
    $('.sort-by-value').on('change', function(e){

      if($('.sort-by-value option:selected').val() == 5){
        document.getElementById('unfinish-quotation').style.display = "block";
        document.getElementById('quotation').style.display = "none";
        $('.table-unfinish-quotations').DataTable().ajax.reload();
      }
      else
      {        
        $('.table-quotation').DataTable().ajax.reload();
        document.getElementById('unfinish-quotation').style.display = "none";
        document.getElementById('quotation').style.display = "block";
      }

    });
    
    $('.selecting-customer').on('change', function(e){
      if($('.sort-by-value option:selected').val() == 5){
        $('.table-unfinish-quotations').DataTable().ajax.reload();
        document.getElementById('unfinish-quotation').style.display = "block";
        document.getElementById('quotation').style.display = "none";
      }
      else{
      $('.table-quotation').DataTable().ajax.reload();
      document.getElementById('unfinish-quotation').style.display = "none";
      document.getElementById('quotation').style.display = "block";
      }
    });

    $('#from_date').change(function() {
      var date = $('#from_date').val();
      if($('.sort-by-value option:selected').val() == 5){
        $('.table-unfinish-quotations').DataTable().ajax.reload();
        document.getElementById('unfinish-quotation').style.display = "block";
        document.getElementById('quotation').style.display = "none";
      }
      else{
      $('.table-quotation').DataTable().ajax.reload();
      document.getElementById('unfinish-quotation').style.display = "none";
      document.getElementById('quotation').style.display = "block";
      }
    });

    $('#to_date').change(function() {
      var date = $('#to_date').val();
      if($('.sort-by-value option:selected').val() == 5){
        $('.table-unfinish-quotations').DataTable().ajax.reload();
        document.getElementById('unfinish-quotation').style.display = "block";
        document.getElementById('quotation').style.display = "none";
      }
      else{
      $('.table-quotation').DataTable().ajax.reload();
      document.getElementById('unfinish-quotation').style.display = "none";
      document.getElementById('quotation').style.display = "block";
      }
    });
    
    // $('.my-orders').on('click',function(){
    //   $("#loader_modal").modal('show');
    //   var sort = $(this).data("id");
    //   $('.sort-by-value').val(sort).change();
    //   setTimeout(function(){ $("#loader_modal").modal('hide'); }, 300);
    // });

    $('.reset').on('click',function(){
      $('#form_id').trigger("reset");
      $('.sort-by-value').val(6).change();
    });
    
    $('.table-quotation').DataTable({
         processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        ordering: false,
        serverSide: false,
        dom: 'ftipr',
        scrollX: true,
        scrollY : '90vh',
        scrollCollapse: true,
        ajax:{
          url:"{!! route('get-completed-quotation') !!}",
          data: function(data) { data.dosortby = $('.sort-by-value option:selected').val(),data.selecting_customer = $('.selecting-customer option:selected').val(),data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val() } ,
        },
        columns: [
            // { data: 'checkbox', name: 'checkbox'},
            { data: 'action', name: 'action' },
            { data: 'ref_id', name: 'ref_id' },
            { data: 'customer', name: 'customer' },
            { data: 'customer_ref_no', name: 'customer_ref_no' },
            { data: 'invoice_date', name: 'invoice_date' },
            { data: 'total_amount', name: 'total_amount' },
            { data: 'target_ship_date', name: 'target_ship_date' },
            { data: 'status', name: 'status' },
        ],
        initComplete: function () {
          // Enable THEAD scroll bars
          $('.dataTables_scrollHead').css('overflow', 'auto');

          // Sync THEAD scrolling with TBODY
          $('.dataTables_scrollHead').on('scroll', function () {
              $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
          });
        }
   });

   $(document).on('click', '.check-all', function () {
        if(this.checked == true){
        $('.check').prop('checked', true);
        $('.check').parents('tr').addClass('selected');
        var cb_length = $( ".check:checked" ).length;
        if(cb_length > 0){
          $('.selected-item').removeClass('d-none');
        }
      }else{
        $('.check').prop('checked', false);
        $('.check').parents('tr').removeClass('selected');
        $('.selected-item').addClass('d-none');
        
      }
    });

   $(document).on('click', '.check', function () {
        var cb_length = $( ".check:checked" ).length;
        var st_pieces = $(this).parents('tr').attr('data-pieces');
        if(this.checked == true){
        $('.selected-item').removeClass('d-none');
        $(this).parents('tr').addClass('selected');
      }else{
        $(this).parents('tr').removeClass('selected');
        if(cb_length == 0){
         $('.selected-item').addClass('d-none');
        }
        
      }
    });

/*     $(document).on('click', '.confirm-btn', function(e){
      // var cs_id = $(this).data('sale-id');
      var selected_products = [];
      $("input.check:checked").each(function() {
        selected_products.push($(this).val());
      });

     
    });
 */

 $('.table-unfinish-quotations').DataTable({
         processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        ordering: false,
        serverSide: false,
        dom: 'ftipr',
        scrollX: true,
        scrollY : '90vh',
        scrollCollapse: true,
        ajax:{
          url:"{!! route('get-pending-quotation') !!}",
          data: function(data) { data.selecting_customer = $('.selecting-customer option:selected').val(),data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val() } ,
        },
        columns: [
            { data: 'checkbox', name: 'checkbox' },
            { data: 'action', name: 'action' },
            { data: 'ref_id', name: 'ref_id' },
            { data: 'customer', name: 'customer' },
            { data: 'customer_ref_no', name: 'customer_ref_no' },
            { data: 'number_of_products', name: 'number_of_products' },
            { data: 'payment_term', name: 'payment_term' },
            { data: 'invoice_date', name: 'invoice_date' },
            { data: 'total_amount', name: 'total_amount' },
            { data: 'status', name: 'status' },
        ],
        initComplete: function () {
          // Enable THEAD scroll bars
          $('.dataTables_scrollHead').css('overflow', 'auto');

          // Sync THEAD scrolling with TBODY
          $('.dataTables_scrollHead').on('scroll', function () {
              $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
          });
        }
   });


   

   $(document).on('click', '.delete-quotations', function(){
          var selected_quots = [];
          $("input.check:checked").each(function() {
            selected_quots.push($(this).val());
          });
          // console.log(selected_quots)
          length = selected_quots.length;

          swal({
                  title: "Alert!",
                  text: "Are you sure to delete all these draft quotations? \n selected draft quotations:"+length,
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
                          dataType:"json",
                          data: {quotations : selected_quots},
                          url:"{{ route('delete-draft-quotations') }}",

                          success:function(result){

                              if(result.success == true){

                                  toastr.success('Success!', 'Draft Quotations deleted Successfully',{"positionClass": "toast-bottom-right"});
                                  window.location.reload();

                              }
                          }
                      });
                  }
                  else{
                      swal("Cancelled", "", "error");
                  }
              });


      });

      $(document).on('click', '.delete-btn', function(e){
          var id = $(this).data('id');
          swal({
                  title: "Alert!",
                  text: "Are you sure you want to delete this draft quotation? ",
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
                          data:'id='+id,
                          url:"{{ route('delete-single-draft-quotation') }}",
                          beforeSend:function(){
                              $('#loader_modal').modal({
                                  backdrop: 'static',
                                  keyboard: false
                              });
                              $("#loader_modal").modal('show');
                          },
                          success:function(response){
                              $("#loader_modal").modal('hide');
                              if(response.success === true){
                                  toastr.success('Success!', response.successmsg ,{"positionClass": "toast-bottom-right"});
                              }else if(respnse.error === true){
                                  toastr.error('Error!', 'Something Went Wrong. Please contact support.' ,{"positionClass": "toast-bottom-right"});
                              }
                              $('.table-unfinish-quotations').DataTable().ajax.reload();
                          }
                      });
                  }
                  else{
                      swal("Cancelled", "", "error");
                  }
              });
      });

  @if(Session::has('successmsg'))
      toastr.success('Success!', "{{ Session::get('successmsg') }}",{"positionClass": "toast-bottom-right"});  
      @php 
       Session()->forget('successmsg');     
      @endphp  
  @endif
  });
</script>
@stop