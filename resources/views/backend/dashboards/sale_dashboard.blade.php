@extends('backend.layouts.layout')
@section('title','Dashboard')


@section('content')

{{-- Content Start from here --}}

<!-- Right Content Start Here -->
<div class="right-contentIn">

<!-- upper section start -->
<div class="row mb-3">


@include('backend.layouts.dashboard-boxes')

</div>

<div class="row align-items-center mb-3">
  <div class="col-md-8 title-col">
      <h4 class="maintitle">My Quotations</h4>
  </div>    
</div>

{{-- <div class="col-lg-9 headings-color">
  <h4>My Quotations</h4>
</div> --}}

<div class="row mb-3 headings-color">
<div class="col-lg-12 headings-color">
  <form id="form_id">
  <div class="row">

    <div class="col">
      <div class="form-group">
        <select class="form-control selecting-tables sort-by-value">
            <option value="1" selected>-- Quotations --</option>
            <option value="6">@if(!array_key_exists('waiting_confrimation', $global_terminologies)) Waiting Confirmation @else {{$global_terminologies['waiting_confrimation']}} @endif</option>
            <option value="5">@if(!array_key_exists('unfinished_quotation', $global_terminologies)) Unfinished Quotation @else {{$global_terminologies['unfinished_quotation']}} @endif</option>
        </select>
      </div>
    </div>

    <div class="col">
      <div class="form-group">
        <select class="form-control selecting-customer">
            <option value="">-- Customers --</option>
            @foreach($customers as $customer)
            <option value="{{$customer->id}}">{{$customer->company}}</option>
            @endforeach
        </select>
      </div>
    </div>

    <div class="col">
      <div class="form-group">
        <input type="date" class="form-control" name="from_date" id="from_date">
      </div>
    </div>

    <div class="col">
      <div class="form-group">
        <input type="date" class="form-control" name="to_date" id="to_date">
      </div>
    </div>

    <div class="col">
      <div class="input-group-append ml-3">
                <span class="reset common-icons" title="Reset">
          <img src="{{asset('public/icons/reset.png')}}" width="27px">
        </span>  
      </div>
    </div>

  </div>
  </form>
</div>    


<div class="row entriestable-row col-lg-12 pr-0 quotation" id="quotation">
 <!--  <div class="selected-item catalogue-btn-group mt-4 mt-sm-3 ml-3 d-none">
      <a href="javascript:void(0);" class="btn selected-item-btn btn-sm success-btn confirm-btn
      confirmImg" data-toggle="tooltip" data-type="rough" data-parcel="1" title="Confirm"><img src="{{ asset('public/site/assets/sales/img/confirm.png') }}"></a>
  </div> -->
  <div class="col-12 pr-0">
    <div class="entriesbg bg-white custompadding customborder">
          <table class="table entriestable table-bordered table-quotation text-center">
              <thead>
                  <tr>
                      <!-- <th>
                          <div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                            <input type="checkbox" class="custom-control-input check-all" name="check_all" id="check-all">
                            <label class="custom-control-label" for="check-all"></label>
                          </div>
                      </th> -->
                      <th>Action</th>
                      <th>Order#</th>
                      <th>Customer #</th>
                      <th>Company</th>
                      <th>Date Purchase</th>
                      <th>Order Total</th>
                      <th>@if(!array_key_exists('target_ship_date', $global_terminologies)) Target Ship Date @else {{$global_terminologies['target_ship_date']}} @endif</th>
                      <th>Status</th>
                  </tr>
              </thead>
               
          </table>
        </div>  
  </div>
</div>
<div class="row entriestable-row col-lg-12 pr-0 unfinish-quotation" id="unfinish-quotation">
 <div class="selected-item catalogue-btn-group mt-4 mt-sm-3 ml-3 d-none">
      <a href="javascript:void(0);" class="btn selected-item-btn btn-sm delete-quotations
      deleteIcon" data-toggle="tooltip" data-type="unfinish-quotation" data-parcel="1" title="delete"><span><i class="fa fa-trash" ></i></span></a>
  </div>
      <div class="col-12 pr-0 ">
          <div class="entriesbg bg-white custompadding customborder">
                <table class="table entriestable table-bordered table-unfinish-quotations text-center">
                    <thead>
                        <tr>
                             <th>
                                <div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                                    <input type="checkbox" class="custom-control-input check-all" name="check_all" id="check-all">
                                <label class="custom-control-label" for="check-all"></label>
                                </div>
                             </th>
                            <th>Action</th>
                            <th>Order#</th>
                            <th>Customer #</th>
                            <th>Company</th>
                            <th>Number of Products</th>
                            <th>Payment Term</th>
                            <th>Invoice Date</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    
              </table>  
          
          </div>
      </div>
</div>
  <div class="modal" id="loader_modal" role="dialog">
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
         "columnDefs": [
    { className: "dt-body-left", "targets": [ 1,2,3,4,6,7 ] },
    { className: "dt-body-right", "targets": [5] }
  ],
        scrollX: true,
        scrollY : '90vh',
        scrollCollapse: true,
        ajax:{
          url:"{!! route('get-completed-quotation-admin') !!}",
          data: function(data) { data.dosortby = $('.sort-by-value option:selected').val(),data.selecting_customer = $('.selecting-customer option:selected').val(),data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val() } ,
        },
        columns: [
            // { data: 'checkbox', name: 'checkbox'},
            { data: 'action', name: 'action' },
            { data: 'ref_id', name: 'ref_id' },
            { data: 'customer_ref_no', name: 'customer_ref_no' },
            { data: 'customer', name: 'customer' },
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
    // $(this).removeClass('d-none');
   $('.delete-quotations').removeClass('d-none');
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
          url:"{!! route('get-pending-quotation-admin') !!}",
          data: function(data) { data.selecting_customer = $('.selecting-customer option:selected').val(),data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val() } ,
        },
        columns: [
            { data: 'checkbox', name: 'checkbox' },
            { data: 'action', name: 'action' },
            { data: 'ref_id', name: 'ref_id' },
            { data: 'customer_ref_no', name: 'customer_ref_no' },
            { data: 'customer', name: 'customer' },
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
                                  // window.location.reload();
                                  $('.table-unfinish-quotations').DataTable().ajax.reload();
                                  $('.delete-quotations').addClass('d-none');
                                  $('.check-all').prop('checked',false);

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