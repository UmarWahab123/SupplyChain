@extends('ecom.layouts.layout')

@section('content')
<style type="text/css">
.invalid-feedback {
     font-size: 100%; 
}
.disabled:disabled{
  opacity:0.5;
  cursor: not-allowed; 
}
table.dataTable thead .sorting { background: url('../public/sort/sort_both.png') no-repeat center right; 
  background-size: 5vh;}
table.dataTable thead .sorting_asc { background: url('../public/sort/sort_asc.png') no-repeat center right;
  background-size: 5vh; }
table.dataTable thead .sorting_desc { background: url('../public/sort/sort_desc.png') no-repeat center right; 
  background-size: 5vh;}
</style>

{{-- Content Start from here --}}

  <!-- header starts -->
  <div class="row d-flex align-items-center left-right-padding mb-2 form-row">
    <div class=" col-lg-3 col-md-4">
      <h3 class="custom-customer-list">@if(!array_key_exists('customer_list', $global_terminologies))Customers List @else {{$global_terminologies['customer_list']}} @endif</h3>
    </div>

  </div>


  {{--Error msgs div--}}
  <div class="row errormsgDiv mt-2" style="display: none;">
    <div class="container" style="max-width: 100% !important; min-width: 100% !important">
      <div class="alert alert-danger alert-dismissible">
        <a href="javascript:void(0)" class="closeErrorDiv">&times;</a>
        <span id="errormsg"></span>
      </div>
    </div>
  </div>

  <!-- header ends -->
<div class="row entriestable-row">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
      <div class="table-responsive">
        <table class="table entriestable table-bordered table-customers text-center">
          <thead>
            <tr>
              <th class="noVis">
                <div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                    <input type="checkbox" class="custom-control-input check-all" name="check_all" id="check-all">
                <label class="custom-control-label" for="check-all"></label>
                </div>
             </th>
              <th>Action</th>
              <th>Customer #</th>
              <th>Reference<br> Name
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="3">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="3">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
              </th>
              <th> {{$global_terminologies['company_name']}}
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="4">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="4">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
              </th>
              <th>Email</th>
              <th>Primary<br>Sales<br> Person</th>
              <th>Secondary <br>Sale<br> Person </th>
              <th>District</th>
              <th>City</th>
              <th>Classification</th>
              <th>Payment Terms</th>
              <th>Customer<br> Since</th>
              <th>Draft<br> Orders</th>
              <th>Total<br> Orders</th>
              <th>Last <br>Order<br> Date</th>
            </tr>
          </thead> 
        </table>
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
 <!-- main content end here -->
</div><!-- main content end here -->




@endsection
@php
      $hidden_by_default = '';
 @endphp
@section('javascript')
<script type="text/javascript">
$(function(e){  
    // Customer Sorting Code Here
  var order = 1;
  var column_name = '';

  $('.sorting_filter_table').on('click',function(){
    $('.arrow_up').children('img').attr("src","{{ url('public/svg/up.svg') }}");
    $('.arrow_down').children('img').attr("src","{{ url('public/svg/down.svg') }}");
    
    order = $(this).data('order');
    column_name = $(this).data('column_name');

    $('.table-customers').DataTable().ajax.reload();

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

    $(document).on("dblclick",".inputDoubleClick",function(){
    // alert($(this).data('id'));
    var str = $(this).data('id');
    if(str !== undefined){

    var res = str.split(" ");
  }else{
    var res = null;
  }
   if(res !== null){
    $(this).addClass('d-none');
      $(this).next().removeClass('d-none');
      $(this).next().addClass('active');
      $(this).next().focus();
      var num = $(this).next().val();
      $(this).next().focus().val('').val(num);

     $.ajax({
    type: "get",
    url: "{{ route('get-salesperson') }}",
    data: 'value='+res[1]+'&choice='+res[0],
     beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal").modal('show');
        },
    success: function(response){
      if(response.field == 'salesperson'){
        console.log(res[2]);
        $('.primary_salespersons_select'+res[2]).append(response.html);
      }
      else if(response.field == 'secondary_salesperson'){
        console.log(res[2]);
        $('.secondary_salespersons_select'+res[2]).append(response.html);
      }      

      // $(this).addClass('d-none');
      // $(this).next().removeClass('d-none');
      // $(this).next().addClass('active');
      // $(this).next().focus();
      // var num = $(this).next().val();
      // $(this).next().focus().val('').val(num);

      $('#loader_modal').modal('hide');
    },
    error: function(request, status, error){
      $('#loader_modal').modal('hide');
    }
  });


 }
   else{
    $(this).addClass('d-none');
      $(this).next().removeClass('d-none');
      $(this).next().addClass('active');
      $(this).next().focus();
      var num = $(this).next().val();
      $(this).next().focus().val('').val(num);
   }

  });

  $(document).on('click', '.check', function () {
    // $(this).removeClass('d-none');
   $('.assigned_to_sales').removeClass('d-none');
   $('.delete_selected_customers').removeClass('d-none');
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

$(document).on('click','.delete_selected_customers',function(){
  // custDeleteIcon
  // alert($('.check-all').val());
   var selected_oi = [];
      var total_received = [];
        $("input.check:checked").each(function() {
          selected_oi.push($(this).val());
        });

        alert(selected_oi);
})

    // $(document).on("focus", ".datepicker", function(){
    //     $(this).datetimepicker({
    //         timepicker:false,
    //         format:'Y-m-d'});
    
    // });
    $(".state-tags").select2({});
    var role = "{{Auth::user()->role_id}}";
    var show = false;
    if(role == 1){
      show = true;
    }
  // New Table Entries 
  var table2 =  $('.table-customers').DataTable({
    processing: true,
    "language": {
    processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    ordering: false,
    dom: 'Blfrtip',
    pageLength: {{50}},
    serverSide: true,
    "lengthMenu": [50,100,150,200],
    "columnDefs": [
      { targets: [{{ ($table_hide_columns != null) ? $table_hide_columns->hide_columns : $hidden_by_default }}], visible: false },
      { className: "dt-body-left", "targets": [ 2,3,4,5,6,7,8,9,10,11,12 ] },
      { className: "dt-body-right", "targets": [ 13,14 ] },
      {
        "targets": [ 0 ],
        "visible": show,
        "searchable": false
      }
    ],
    scrollX:true,
    scrollY : '90vh',
    scrollCollapse: true,
    buttons: [
      {
        extend: 'colvis',
        columns: ':not(.noVis)',
      }
    ],
    ajax: 
    {
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      url: "{!! route('get-ecom-customer') !!}",
      data: function(data) {
      data.user_id = $('.sales_persons option:selected').val(),
      data.sortbyparam = column_name, 
      data.sortbyvalue = order } ,
    },
    columns: [
        { data: 'checkbox', name: 'checkbox' },
        { data: 'action', name: 'action' },
        { data: 'reference_number', name: 'reference_number' },
        { data: 'reference_name', name: 'reference_name' },
        { data: 'company', name: 'company' },
        { data: 'email', name: 'email' },
        { data: 'user_id', name: 'user_id' },
        { data: 'secondary_sp', name:'secondary_sp'},
        { data: 'city', name: 'city' },
        { data: 'state', name: 'state' },
        { data: 'category', name: 'category' },
        { data: 'credit_term', name: 'credit_term' },
        { data: 'created_at', name: 'created_at' },
        { data: 'draft_orders', name: 'draft_orders' },
        { data: 'total_orders', name: 'total_orders' },
        { data: 'last_order_date', name: 'last_order_date' },            
    ],
    initComplete: function () {
    // Enable THEAD scroll bars
    $('.dataTables_scrollHead').css('overflow', 'auto');
    // Sync THEAD scrolling with TBODY
      $('.dataTables_scrollHead').on('scroll', function () {
          $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
      });
    },
    drawCallback: function(){
      $('#loader_modal').modal('hide');
    },
  });

  table2.on( 'column-visibility.dt', function ( e, settings, column, state ) {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.post({
        url : "{{ route('toggle-column-display') }}",
        dataType : "json",
        data : {type:'customer_list',column_id:column},
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
        error: function(request, status, error)
        {
          $('#loader_modal').modal('hide');
        }
      });
    });

  $('.dataTables_filter input').unbind();
  $('.dataTables_filter input').bind('keyup', function(e) {
  if(e.keyCode == 13) 
  {
    table2.search($(this).val()).draw();
  }
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
          url: "{{ route('add-customer-note') }}",
          dataType: 'json',
          type: 'post',
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
      url: "{{ route('get-customer-note') }}",
      data: 'customer_id='+sid,
      beforeSend: function(){
        var loader_img = "{{ url('public/uploads/gif/waiting.gif') }}";
        var loader_html = '<div class="d-flex justify-content-center"><img class="img-spinner" src="'+loader_img+'" style="margin-top: 10px;"></div>';
        $('.fetched-notes').html(loader_html);
      },
      success: function(response){
        $('.fetched-notes').html(response);
      },
      error: function(request, status, error){

      }
    });

  });

  $(document).on('keyup', '.form-control', function(){
      $(this).removeClass('is-invalid');
      $(this).next().remove();
    });  

  // delete Customer
  $(document).on('click', '.custDeleteIcon', function(e){

    var id = $(this).data('id');
      swal({
        title: "Are you sure?",
        text: "You want to delete this Customer ?",
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
              url: "{{ route('delete-customer') }}",
              beforeSend: function(){
                $('#loader_modal').modal({
                  backdrop: 'static',
                  keyboard: false
                });
                $('#loader_modal').modal('show');
              },
              success: function(response){
                $('#loader_modal').modal('hide');
                if(response.success == true){
                  toastr.success('Success!', 'Customer Deleted Successfully.',{"positionClass": "toast-bottom-right"});
                  window.location.reload();
                }
                else
                {
                  toastr.error('Error!', 'Customer Can\'t be deleted invoices Attached!',{"positionClass": "toast-bottom-right"});
                }
              },
              error: function(request, status, error){
                $('#loader_modal').modal('hide');
              }
            });
          }
          else {
              swal("Cancelled", "", "error");
          }
      });
    });

  // delete Customer Shipping Info
  $(document).on('click', '.delete-note', function(e){
    var id = $(this).data('id');
    swal({
      title: "Are you sure?",
      text: "You want to delete this Customer Note?",
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
            url: "{{ route('delete-customer-note-info') }}",
            beforeSend: function(){
              $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
              });
              $('#loader_modal').modal('show');
            },
            success: function(response){
              $('#loader_modal').modal('hide');
              if(response.success == true){
               $("#cust-note-"+id).remove();
              }                
            },
            error: function(request, status, error){
              $('#loader_modal').modal('hide');
            }
          });
        }
        else {
            swal("Cancelled", "", "error");
        }
    });
  });

    //Suspend or deactivate customer
  $(document).on('click', '.suspend-customer', function(){
    var id = $(this).data('id');
    swal({
        title: "Alert!",
        text: "Are you sure you want to suspend this Customer?",
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
              data:{id:id,type:'customer'},
              url:"{{ route('customer-suspension') }}",
              beforeSend:function(){
                 $('#loader_modal').modal({
                      backdrop: 'static',
                      keyboard: false
                    });
                 $("#loader_modal").modal('show');
              },
              success:function(data){
                $("#loader_modal").modal('hide');
                  if(data.error == false){
                    toastr.success('Success!', data.successmsg ,{"positionClass": "toast-bottom-right"});
                    setTimeout(function(){
                      window.location.reload();
                    }, 2000);
                  }
              },
              error: function(request, status, error){
                $('#loader_modal').modal('hide');
              }
           });
        } 
        else{
            swal("Cancelled", "", "error");
        }
   });  
  });

  $(document).on('click', '.activateIcon', function(){
    var id = $(this).data('id');
    swal({
        title: "Alert!",
        text: "Are you sure you want to activate this Customer?",
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
              data:{id:id},
              url:"{{ route('customer-activation') }}",
              beforeSend:function(){
                 $('#loader_modal').modal({
                      backdrop: 'static',
                      keyboard: false
                    });
                 $("#loader_modal").modal('show');
              },
              success:function(data){
                $("#loader_modal").modal('hide');
                  if(data.error == false){
                    toastr.success('Success!', data.successmsg ,{"positionClass": "toast-bottom-right"});
                    setTimeout(function(){
                      window.location.reload();
                    }, 2000);
                  }
              },
              error: function(request, status, error){
                $('#loader_modal').modal('hide');
              }
           });
        } 
        else{
            swal("Cancelled", "", "error");
        }
   });  
  });

});  
</script>

<script type="text/javascript">
$(".state-tags").select2({
  tags: true
});

// Delete customer
$(document).on('click','.delete-customer',function(){
  var customer_id = $(this).data('id');
  swal({
    title: "Are You Sure?",
    text: "You want to delete customer!!!",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#DD6B55",
    confirmButtonText: "Yes, do it!",
    cancelButtonText: "Cancel",
    closeOnConfirm: true,
    closeOnCancel: false
    },
  function (isConfirm) {
    if (isConfirm)
    {
      $.ajax({
      method: "get",
      url: "{{ url('sales/delete-customer') }}",
      dataType: 'json',
      data: {id:customer_id},
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      success: function(data)
      {
        $('#loader_modal').modal('hide');
        if(data.success == true)
         {
            toastr.success('Success!', 'Customer deleted successfully.',{"positionClass": "toast-bottom-right"});
            window.location.href = "{{ url('sales/customer') }}";
          } 
      },
      error: function(request, status, error){
        $('#loader_modal').modal('hide');
      }
      });
    }
    else 
    {
      $('#loader_modal').modal('hide');
      swal("Cancelled", "", "error");
      check[0].checked = false;
      if(check[1]){
      check[1].checked = false;
      }
      document.getElementById('is_default_value').value = 0;    
    }
  });
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
                      beforeSend: function(){
                        $('#loader_modal').modal({
                          backdrop: 'static',
                          keyboard: false
                        });
                        $('#loader_modal').modal('show');
                      },
                      success:function(result){
                        $('#loader_modal').modal('hide');
                          if(result.success == true){

                              toastr.success('Success!', 'Customers Assigned Successfully',{"positionClass": "toast-bottom-right"});
                              // window.location.reload();
                              $('.table-customers').DataTable().ajax.reload();
                              $('.assigned_to_sales').addClass('d-none');
                              $('#sales_modal').modal('hide');
                              $('.check-all').prop('checked',false);

                              location.reload();

                          }
                      },
                      error: function(request, status, error){
                        $('#loader_modal').modal('hide');
                      }
                  });
              }
              else{
                  swal("Cancelled", "", "error");
              }
          });

});

// delete Customer
$(document).on('click', '.delete-btn', function(e){
  var selected_options = [];
  $("input.check:checked").each(function() {
    selected_options.push($(this).val());
  });

  swal({
    title: "Are you sure?",
    text: "You want to delete selected Customer(s) ?",
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
        data:'customers='+selected_options,
        url: "{{ route('delete-customers-permanent') }}",
        beforeSend: function(){
          $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
          $('#loader_modal').modal('show');
        },
        success: function(data){
          $('#loader_modal').modal('hide');
          if(data.success == true)
          {
            toastr.success('Success!', 'Customer(s) deleted successfully.',{"positionClass": "toast-bottom-right"});
            $('.table-customers').DataTable().ajax.reload();
            $('.selected-item').addClass('d-none');
          }
          else
          {
            $('.errormsgDiv').show();
            $('#errormsg').html(data.errorMsg);
            // toastr.error('Error!', 'Some Customer(s) can\'t be deleted, they exist in following Orders.',{"positionClass": "toast-bottom-right"});
            $('.table-customers').DataTable().ajax.reload();
            $('.selected-item').addClass('d-none');
          }
        },
        error: function(request, status, error){
          $('#loader_modal').modal('hide');
        }
      });
    }
    else {
        swal("Cancelled", "", "error");
    }
  });
});

$(document).on('click', '.closeErrorDiv', function (){
  $('.errormsgDiv').hide();
});


</script>
@stop

