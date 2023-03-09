

@extends($layout.'.layouts.layout')

@section('title','Users Management | Supply Chain')

@section('content')
<style type="text/css">
.invalid-feedback {
     font-size: 100%; 
}
.disabled:disabled{
  opacity:0.5;
  cursor: not-allowed; 
}
.select2-container{
  width: 100% !important;
}
</style>

{{-- Content Start from here --}}
<div class="row mb-0">
  <div class="col-lg-3 col-md-6 title-col">
    <div class="d-sm-flex justify-content-between">
      <h4 class="text-uppercase fontbold">Customers List</h4>
      <div class="mb-0">
        <!-- <a class="btn button-st" href data-toggle="modal" data-target="#addPurchasingModal">
          Add User
        </a> -->
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 mb-1">
      <select class="font-weight-bold form-control-lg form-control customers_status" name="customers_status" >
        <option value="" disabled="" selected="">Choose Status</option>
        <option value="1" selected="true">Completed</option>
         @if(session('msg'))
          <option value="0" selected="true">Incomplete</option>
          @else
          <option value="0">Incomplete</option>
          @endif
  
        <option value="2">Deleted</option>
      </select>
    </div>
     <div class="col-lg-3 col-md-6 mb-1">
      <select class="font-weight-bold form-control-lg form-control sales_persons" name="sales_persons" >
        <option value="" disabled="true" selected="true">Choose Sale Person</option>
        @if(@$users)
            @foreach($users as $user)
            <option value="{{$user->id}}">{{$user->name}}</option>
            @endforeach
            @endif
      </select>
    </div>
     <div class="col-lg-3 col-md-6">
      <select class="font-weight-bold form-control-lg form-control customers_type" name="customers_type" >
        <option value="" disabled="true" selected="true">Choose Customers Type</option>
        <option value="1">Primary Customers</option>
        <option value="0">Secondary Customers</option>
      </select>
    </div>
</div>


<div class="row entriestable-row">
  <div class="col-12">
    <div class="selected-item catalogue-btn-group mb-2 d-none">
      <a href="javascript:void(0);" class="btn-color btn text-uppercase purch-btn headings-color assigned_to_sales" data-toggle="modal" data-target="#sales_modal" data-parcel="1" title="Assign Customers"><span>Assign Sales Person</span></a>
  </div>
    <div class="entriesbg bg-white custompadding customborder">
        <div class="">
          <table class="table entriestable table-bordered table-customers text-center table-responsive">
              <thead>
                  <tr>

                     <th>
                                <div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                                    <input type="checkbox" class="custom-control-input check-all" name="check_all" id="check-all">
                                <label class="custom-control-label" for="check-all"></label>
                                </div>
                             </th>
                      <th>Action</th>
                      <th>Reference Name</th>
                      <th>{{$global_terminologies['company_name']}} </th>
                      <th>Email</th>
                      <th width="30%">Sales Person</th>
                      <th>Distric</th>
                      <th>City</th>
                      <!-- <th>Contact Name</th> -->
                      <th>Classification</th>
                      <th>Customer Since</th>
                      <th>Draft Orders</th>
                      <th>Total Orders</th>
                      <th>Last Order Date</th>
                      <th>Note</th>
                      <th>Status</th>
                      
                      <!-- <th>Notes</th> -->
                  </tr>
              </thead>
               
          </table>
        </div>  


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

<!-- Modal For Note -->
<div class="modal" id="sales_modal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Select Sales Person</h4>
        <button type="button" class="close close-btn" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal body -->
      <form role="form" class="" method="post">
      <div class="modal-body">
        <div class="row">
              <div class="col-md-12">
                      <div class="row">
                          <div class="col-xs-12 col-md-12">
                              <div class="form-group"> 
                                <div class="incomplete-filter">  
                                <select class="font-weight-bold form-control-lg form-control js-states state-tags sales_assign_select" name="category" required="true">
                                  <option value="" disabled="" selected="">Choose Sales Person</option>
                                  @if(@$users)
                                  @foreach($users as $user)
                               
                                    <option value="{{$user->id}}">{{$user->name}}</option>
                                  
                                
                                  @endforeach
                                  @endif
                                </select>
                              </div>
                              </div>
                          </div>
                      </div>
              </div>
        </div>
      </div>

      <!-- Modal footer -->

     </form>

    </div>
  </div>
</div>

<!-- Modal For Note -->
<div class="modal" id="add_notes_modal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Add Customer Notes</h4>
        <button type="button" class="close close-btn" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal body -->
      <form role="form" class="add-cust-note-form" method="post">
      <div class="modal-body">
        <div class="row">
              <div class="col-md-12">
                      <div class="row">
                          <div class="col-xs-12 col-md-12">
                              <div class="form-group d-none"> 
                                <label>Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" placeholder="Note Title" value="note" name="note_title">
                              </div>
                              <div class="form-group"> 
                                <label>Description <span class="text-danger">*</span> <small>(255 Characters Max)</small></label>
                                <textarea class="form-control" placeholder="Note Description" rows="6" name="note_description" maxlength="255"></textarea>
                              </div>
                          </div>
                      </div>
              </div>
        </div>
      </div>

      <!-- Modal footer -->
      <div class="modal-footer">
        <input type="hidden" name="customer_id" class="note-customer-id">
        <button class="btn btn-success" type="submit" class="save-btn" ><i class="fa fa-floppy-o"></i> Save </button>
        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
      </div>
     </form>

    </div>
  </div>
</div>

{{-- Customer Notes Modal --}}
<div class="modal" id="notes-modal">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Customer Notes</h4>
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
   $('.assigned_to_sales').removeClass('d-none');
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

  $(function(e){
    $(".state-tags").select2({});
    var role = "{{Auth::user()->role_id}}";
    var show = false;
    if(role == 1){
      show = true;
    }
    $('.table-customers').DataTable({
         processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        ordering: false,
        serverSide: true,
        fixedHeader: true,
        scrollX: true,
    scrollY : '90vh',
    scrollCollapse: true,
        colReorder: {
          realtime: false,          
        },
        pageLength: {{100}},
        lengthMenu: [ 100, 200, 300, 400],

        "columnDefs": [
            {
                "targets": [ 0 ],
                "visible": show,
                "searchable": false
            },
            { className: "dt-body-left", "targets": [ 2,3,4,5,6,7,8,9,12 ] },
    { className: "dt-body-right", "targets": [ 10,11 ] }
        ],

        // ajax: "{{route('common-customer-list-data')}}",
         ajax: 
        {
          url: "{!! route('common-customer-list-data') !!}",
          data: function(data) { data.customers_status = $('.customers_status option:selected').val(),data.user_id = $('.sales_persons option:selected').val(),data.customers_type = $('.customers_type option:selected').val()} ,
        },
        columns: [
            { data: 'checkbox', name: 'checkbox' },
            { data: 'action', name: 'action' },
            { data: 'reference_name', name: 'reference_name' },
            { data: 'company', name: 'company' },
            { data: 'email', name: 'email' },
            { data: 'user_id', name: 'user_id' },
            { data: 'state', name: 'state' },
            { data: 'city', name: 'city' },
            // { data: 'name', name: 'name' },
            { data: 'category_id', name: 'category_id' },
            { data: 'created_at', name: 'created_at' },
            { data: 'draft_orders', name: 'draft_orders' },
            { data: 'total_orders', name: 'total_orders' },
            { data: 'last_order_date', name: 'last_order_date' },
            { data: 'notes', name: 'notes' },
            { data: 'status', name: 'status' },
            
            // { data: 'notes', name: 'notes' },
        ],
        initComplete: function () {
        // Enable THEAD scroll bars
        $('.dataTables_scrollHead').css('overflow', 'auto');
        $('.dataTables_scrollHead').on('scroll', function () {
          $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
        });

      },
    });
    
  });
   $(document).on('change','.customers_status',function(){
    $(".sales_persons").val($(".sales_persons option:first").val());
    $(".customers_type").val($(".customers_type option:first").val());
    
    var selected = $(this).val();
    if($('.customers_status option:selected').val() != '')
    {
      $('.table-customers').DataTable().ajax.reload();
    }
  });

      $(document).on('change','.sales_persons',function(){
    $(".customers_type").val($(".customers_type option:first").val());

    var selected = $(this).val();
    if($('.sales_persons option:selected').val() != '')
    {
      $('.table-customers').DataTable().ajax.reload();
    }
  });

    $(document).on('change','.customers_type',function(){
    var selected = $(this).val();
    if($('.customers_type option:selected').val() != '')
    {
      $('.table-customers').DataTable().ajax.reload();
    }
  });

  $(".sales_assign_select").select2().on("select2:close", function(e) {
    // alert($(this).val());
    var user_id = $(this).val();
    if(user_id == null){
      return;
    }
        var selected_options = [];
          $("input.check:checked").each(function() {
            selected_options.push($(this).val());
          });
        length = selected_options.length;

          swal({
                  title: "Alert!",
                  text: "Are you sure to want to assign these customers to other sales person!",
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
                          data: {customers : selected_options,user_id:user_id},
                          url:"{{ route('assign-customers-to-sale') }}",

                          success:function(result){

                              if(result.success == true){

                                  toastr.success('Success!', 'Customers Assigned Successfully',{"positionClass": "toast-bottom-right"});
                                  // window.location.reload();
                                  $('.table-customers').DataTable().ajax.reload();
                                  $('.assigned_to_sales').addClass('d-none');
                                  $('#sales_modal').modal('hide');
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

  $(document).on('click', '.add-notes', function(e){
      var customer_id = $(this).data('id');
      $('.note-customer-id').val(customer_id);
      // alert(customer_id);

    }); 

  $('.add-cust-note-form').on('submit', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('add-customer-note-common') }}",
          dataType: 'json',
          method: 'post',
          data: new FormData(this), 
          contentType: false,       
          cache: false,             
          processData:false,   
          beforeSend: function(){
            $('.save-btn').addClass('disabled');
            $('.save-btn').attr('disabled', true);
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('show');
          },
          success: function(result){
            $('.save-btn').attr('disabled', true);
            $('.save-btn').removeAttr('disabled');
            $('#loader_modal').modal('hide');
            if(result.success == true){
              toastr.success('Success!', 'Note added successfully',{"positionClass": "toast-bottom-right"});
              /*setTimeout(function(){
                window.location.reload();
              }, 2000);*/
              
              $('.add-cust-note-form')[0].reset();
              $('#add_notes_modal').modal('hide');
              
            }else{
              toastr.error('Error!', result.errormsg,{"positionClass": "toast-bottom-right"});
            }
            
          },
          error: function (request, status, error) {
                /*$('.form-control').removeClass('is-invalid');
                $('.form-control').next().remove();*/
                $('#loader_modal').modal('hide');
                $('.save-btn').removeClass('disabled');
                $('.save-btn').removeAttr('disabled');
                json = $.parseJSON(request.responseText);
                $.each(json.errors, function(key, value){
                      $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                     $('input[name="'+key+'"]').addClass('is-invalid');
                     $('textarea[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                     $('textarea[name="'+key+'"]').addClass('is-invalid');

                  
                });
            }
        });
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
      url: "{{ route('get-customer-note-common') }}",
      data: 'customer_id='+sid,
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
  </script>

@stop

