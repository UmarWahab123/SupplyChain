<!doctype html>
<html lang="en">
<head>
<!-- Required meta tags -->
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="_token" content="{{csrf_token()}}" />
<title>{{$sys_name}} | @yield('title')</title>
{{-- <link rel="shortcut icon" href="{{asset('public/img/logo-icon.png')}}"> --}}
@if ($sys_logos->favicon != null)
  <link rel="shortcut icon" href="{{asset('public/uploads/logo/'.$sys_logos->favicon)}}">
@else
  <link rel="shortcut icon" href="{{asset('public/img/logo-icon.png')}}">
@endif

<!-- Bootstrap CSS -->
<link href="{{asset('public/site/assets/backend/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<!-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css"> -->
<link rel="stylesheet" type="text/css" href="{{asset('public/site/assets/backend/css/jquery.dataTables.min.css')}}">

<link href="{{ asset('public/'.mix('/css/style.css')) }}" rel="stylesheet">
<link href="{{asset('public/site/assets/backend/css/custom.css?')}}{{ time() }}" rel="stylesheet" type="text/css">
<link href="{{asset('public/site/assets/backend/css/style.css?')}}{{ time() }}" rel="stylesheet" type="text/css">
<link href="{{asset('public/site/assets/backend/css/mobile_header.css?')}}{{ time() }}" rel="stylesheet" type="text/css">

{{-- Sweet Alert --}}
<link href="{{asset('public/site/assets/backend/css/sweetalert.min.css')}}" rel="stylesheet">
{{-- Toastr Plugin --}}
<link href="{{asset('public/site/assets/backend/css/toastr.min.css')}}" rel="stylesheet">
{{-- JQuery UI Datepicker --}}
<link href="https://cdnjs.cloudflare.com/ajax/libs/datepicker/0.6.5/datepicker.min.css" rel="stylesheet">
{{-- JQuery UI --}}
<!-- <link href="{{asset('public/site/assets/backend/css/jquery.datetimepicker.min.css')}}" rel="stylesheet"> -->
<!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css"> -->
{{-- For select2 --}}
<link href="{{asset('public/site/assets/backend/css/select2.min.css')}}" type="text/css" rel="stylesheet" />

{{--TOOLTIP--}}
<link href="{{asset('public/site/assets/backend/css/qtip.css')}}" type="text/css" rel="stylesheet" />

<link rel="stylesheet" href="{{asset('public/site/assets/backend/css/bootstrap-select.min.css')}}">
{{-- DataTables Fixed Header --}}
<link rel="stylesheet" href="{{asset('public/site/assets/backend/css/fixedHeader.dataTables.min.css')}}">

{{-- DataTables Buttons --}}
<link rel="stylesheet" href="{{asset('public/site/assets/backend/css/buttons.dataTables.min.css')}}">

{{-- Offline feature css --}}
<link rel="stylesheet" href="{{asset('public/site/assets/offline-language-english.css')}}">
<link rel="stylesheet" href="{{asset('public/site/assets/offline-theme-chrome.css')}}">
{{-- Datatable ColReorder --}}
<link rel="stylesheet" type="text/css" href="{{asset('public/site/assets/backend/css/dataTables.colReorder.min.css')}}">
{{--  <link href="https://nightly.datatables.net/scroller/css/scroller.dataTables.css?_=ba19b957f4cc4b35b31db5536fb7eb7b.css" rel="stylesheet" type="text/css" /> --}}

@if(strpos(Request::url(),'http://wholesale.d11u.com') !== false || strpos(Request::url(),'https://wholesale.d11u.com') !== false)
<style type="text/css">
  .sidebarbg{
    background: red !important;

  }
  .sidebarnav ul .nav-link{
    border-top: 1px solid white;
  }
  .recived-button{
    background-color: red;
    border: 1px solid white;
  }
  .btn-color{
    background-color: red;
    border: 1px solid red;
  }
  .purch-btn{
    background-color: red;
    border: 1px solid red;
  }
  .btn{
    background-color: red;
    border: 1px solid red;

  }
</style>

@endif
@include('layouts.custom_color')
</head>
<body>

<div class="wrapper">
 <input type="hidden" name="get_url" id="get_url" value="{{url('')}}">
 {{-- header code --}}
 @include('importing.layouts.header')

<div class="main-content">

 {{-- Left side bar --}}
 @include('general.general_sidebar')


 
 <!-- Right Content Start Here -->
 <div class="right-content">

  @yield('content')

 </div>
 <!-- Right Content Ended Here -->
  

</div>




<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<!-- <script src="assets/js/jquery-3.3.1.slim.min.js"></script> -->

<script src="{{asset('public/site/assets/backend/js/jquery.min.js')}}"></script>
<script src="{{asset('public/site/assets/backend/js/popper.min.js')}}"></script>
<script src="{{asset('public/site/assets/backend/js/bootstrap.min.js')}}"></script>
<!-- <script src="{{asset('public/site/assets/sales/js/menuscript2.js')}}"></script> -->

<script src="{{asset('public/site/assets/sales/js/menuscript.js')}}"></script>
<script src="{{asset('public/site/assets/backend/js/menuscript.js')}}"></script>
<script src="{{asset('public/site/assets/theme-custom.js')}}"></script>
<script src="{{asset('public/site/assets/backend/js/bootstrap-tagsinput.js')}}"></script>

{{-- jquery ui --}}
<!-- <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script> -->
<script src="{{asset('public/site/assets/backend/js/jquery-ui.js')}}"></script>
{{-- Toastr Plugin --}}
<script  src="{{asset('public/site/assets/backend/js/toastr.min.js')}}"></script>

<!-- DataTables -->
<script  src="{{asset('public/site/assets/backend/js/jquery.dataTables.js')}}"></script>
<script src="{{asset('public/site/assets/backend/js/jquery.dataTables.ColReorder.js')}}"></script>
<script src="{{asset('public/site/assets/backend/js/datatble-input-pagination.js')}}"></script>


{{-- TOOLTIP--}}
<script  src="{{asset('public/site/assets/backend/js/qtip.js')}}"></script>

<script src="{{asset('public/site/assets/backend/js/ckeditor.js')}}"></script>
<!-- <script src="https://cdn.ckeditor.com/ckeditor5/11.2.0/classic/ckeditor.js"></script> -->

{{-- Sweet Alert --}}
<script  src="{{asset('public/site/assets/backend/js/sweetalert.min.js')}}"></script>

{{-- bootstrap-select --}} 
<script src="{{asset('public/site/assets/backend/js/bootstrap-select.min.js')}}"></script>

{{-- Jquery Datepicker --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/datepicker/0.6.5/datepicker.min.js"></script>
{{-- Jquery Timepicker --}}
<!-- <script src="{{asset('public/site/assets/backend/js/jquery.datetimepicker.full.min.js')}}"></script> -->

<script src="{{asset('public/site/assets/backend/js/custom.js')}}"></script>
{{-- FancyBox --}}
<script src="{{asset('public/site/assets/backend/js/fancybox.min.js')}}"></script>

{{-- Modernizer --}}
<script src="{{ asset('public/site/assets/backend/js/modernizr.min.js') }}"></script>

{{-- UploadHBR --}}
<script src="{{ asset('public/site/assets/multi-Images/js/uploadHBR.min.js') }}"></script>

{{-- Datatable Fixed Header --}}
<script src="{{ asset('public/site/assets/backend/js/dataTables.fixedHeader.min.js') }}"></script>

{{-- Datatable Buttons --}}
<script src="{{ asset('public/site/assets/backend/js/dataTables.buttons.min.js') }}"></script>

{{-- Datatable Col Visibility --}}
<script src="{{ asset('public/site/assets/backend/js/buttons.colVis.min.js') }}"></script>

{{-- For select2 --}}
<script src="{{asset('public/site/assets/backend/js/select2.full.min.js')}}"></script>

<script src="{{asset('public/site/assets/jquerysession.js')}}"></script>
<script src="{{asset('public/js/autologout.js')}}"></script>
<script src="{{asset('public/js/custom.js')}}"></script>

{{-- For offline --}}
<script src="{{asset('public/site/assets/offline.min.js')}}"></script>
<script type="text/javascript" src="{{asset('public/js/offline_script.js')}}"></script>
<script src="{{asset('public/site/assets/backend/js/scroller.min.js')}}"></script>
<!-- <script src="https://nightly.datatables.net/scroller/js/dataTables.scroller.js?_=ba19b957f4cc4b35b31db5536fb7eb7b"></script> -->


<script type="text/javascript">
  $(document).ready(function(){
  $('[data-toggle="tooltip"]').tooltip();
});
$(document).ready(function() {
                    var getUrl = window.sessionStorage.getItem('url');
                    if(window.location.href == getUrl)
                    {
                        $('.filters_div select').each(function(){
                            var name = $(this).attr('class');
                            if(name)
                            {
                                var new_name = name.split(/\s/).join('');
                                // alert(new_name);
                                var getsession = window.sessionStorage.getItem(new_name);
                                // alert(getsession);
                                if(getsession != null )
                                {  
                                    $(this).val(getsession);
                                }
                            }

                        });
                        $('.filters_div input').each(function(){
                            var name = $(this).attr('class');
                            if(name)
                            {
                                var new_name = name.split(/\s/).join('');
                                // alert(new_name);
                                var getsession = window.sessionStorage.getItem(new_name);
                                if(getsession != null)
                                {
                                    var type = $(this).attr('type');
                                    if(type == 'radio')
                                    {
                                        $(this).prop('checked',true);
                                    }
                                    else
                                    {
                                        $(this).val(getsession);
                                    }
                                }
                            }

                        });
                    }
                    else
                    {
                        // alert('here');
                        window.sessionStorage.clear();
                    }
                    // alert(getsession);
                    // var names = $('select').attr('name');
                    // $('.'+names).val(getsession);

                    $('.filters_div select').on('change',function(){
                        // var nam = $(this).attr('name');
                        var name = $(this).attr('class');
                        if(name)
                        {
                            var new_name = name.split(/\s/).join('');
                            if($(this).hasClass('select2-hidden-accessible'))
                            {
                                var new_name = new_name.replace("select2-hidden-accessible", "");
                            }
                            var setsession = window.sessionStorage.setItem(new_name, $(this).val());
                            var seturl = window.sessionStorage.setItem('url', window.location.href);
                        }
                        
                    });

                    $('.filters_div input').on('change',function(){
                            var name = $(this).attr('class');
                            if(name)
                            {
                                var new_name = name.split(/\s/).join('');
                                // alert(new_name);
                                var setsession = window.sessionStorage.setItem(new_name, $(this).val());
                                var seturl = window.sessionStorage.setItem('url', window.location.href);
                            }
                            
                    });

                    if(window.location.href == getUrl)
                    {
                      $('.filters_div .reset-btn').on('click',function(){
                        window.sessionStorage.clear();
                      });
                      $('.filters_div .reset').on('click',function(){
                        window.sessionStorage.clear();
                      })
                    }

            })

// (function($) {
//   'use strict';
//   $(function() {
//     var current = location.pathname.split("/").slice(-1)[0].replace(/^\/|\/$/g, '');
//     $('.nav-item').each(function() {
//       var $this = $(this);
//       if (current === "") {
//         //for root url
//         if ($this.find(".nav-link").attr('href').indexOf("index.html") !== -1) {
//           $(this).find(".nav-link").parents('.nav-item').last().addClass('active');
//           $(this).addClass("active");
//         }
//       } else {
//         //for other url
//         if ($this.find(".nav-link").attr('href').indexOf(current) !== -1) {
//           $(this).find(".nav-link").parents('.nav-item').last().addClass('active');
//           $(this).addClass("active");
//         }
//       }
//     })
//   });
// })
(jQuery);




$(document).ready(function() {
$('#example').dataTable({
    // "bPaginate": false,
    "bLengthChange": false,
    // "bFilter": true,
    // "bInfo": false,
    // "bAutoWidth": false });
});
});


</script>

<script>
  $(function(){
    $.fn.dataTable.ext.errMode = 'throw';
    var activeurl = window.location;
    $('a[href="'+activeurl+'"]').parents('li').addClass('active');
    
    $('.close-btn').on('click', function(e){
      $('.modal').modal('hide');
    });

    $(document).on('change', '.customer-select', function(e){
    var action = '';
    var selected_customer = [];
     $.each($(".customer-select option:selected"), function(){            
            selected_customer.push($(this).val());
        });

     $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });

     if(selected_customer == '')
     {
      toastr.error('Error!', 'Please Select Customer First',{"positionClass": "toast-bottom-right"});
      return;
     }

     $.ajax({
              method:"post",
              data:'selected_customer='+selected_customer+'&action='+action,
              url:"{{ route('product-order-invoice') }}",
              beforeSend:function(){
                $('#loader_modal').modal({
                    backdrop: 'static',
                    keyboard: false
                  });
                 $("#loader_modal").modal('show');
              },
              success:function(data){
                $("#loader_modal").modal('hide');
                if(data.success == true){
                  window.location.href = "{{ url('sales/get-invoice') }}"+"/"+data.id;
                }
                else{
                     var html_str = `<div class="">
                                <div class="openinv">
                                <table class="table">
                                  <tbody>`;
                                  for(var i = 0; i < data.invoices.length; i++){
                          html_str +=   `<tr>
                                      <td>`+data.invoices[i].ref_id+`<span>(`+data.invoices[i].order_products.length+`)</span></td>
                                      <td><a href="javascript:void(0);" class="add-to-invoice"   data-sale-id="`+data.invoices[i].id+`">ADD</a></td>
                                    </tr>`;
                                    }           
                         html_str += `</tbody>
                                </table>
                              </div>`;
                     // $('#create-custom-badge-order').modal('hide');         
                     // $('#invoiceModal').modal('show'); 
                     $('.curr-order-quotation').empty();            
                     $('.curr-order-quotation').html(html_str); 
                 
                }
              }
           });
  });
  
  $(document).on('click', '.create-new-quo', function(e){
    var selected_customer = [];
    var action = '';
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });

    $.each($(".customer-select option:selected"), function(){            
          selected_customer.push($(this).val());
      });
            
      
      if($(this).data('action') == 'new'){
      action = 'new';
    }

     if(selected_customer == '')
     {
      toastr.error('Error!', 'Please Select Customer First',{"positionClass": "toast-bottom-right"});
      return;
     }
    
    $.ajax({
            method:"post",
            data:'selected_customer='+selected_customer+'&action='+action,
            url:"{{ route('product-order-invoice') }}",
            beforeSend:function(){
              $('#loader_modal').modal({
                  backdrop: 'static',
                  keyboard: false
                });
               $("#loader_modal").modal('show');
            },
            success:function(data){
              $("#loader_modal").modal('hide');
              if(data.success == true){
                window.location.href = "{{ url('sales/get-invoice') }}"+"/"+data.id;
              }
              else{
                   var html_str = `<div class="">
                              <div class="openinv">
                              <table class="table">
                                <tbody>`;
                                for(var i = 0; i < data.invoices.length; i++){
                        html_str +=   `<tr>
                                    <td>`+data.invoices[i].ref_id+`<span>(`+data.invoices[i].order_products.length+`)</span></td>
                                    <td><a href="javascript:void(0);" class="add-to-invoice"   data-sale-id="`+data.invoices[i].id+`">ADD</a></td>
                                  </tr>`;
                                  }           
                       html_str += `</tbody>
                              </table>
                            </div>`;
                   // $('#create-custom-badge-order').modal('hide');         
                   // $('#invoiceModal').modal('show');         
                   $('.curr-order-quotation').html(html_str); 
               
              }
            }
         });
}); 

  $(document).on('click', '.add-to-invoice', function(e){
      var selected_customer = [];
      var cs_id = $(this).data('sale-id');     
       $.each($(".customer-select option:selected"), function(){            
          selected_customer.push($(this).val());
      });

      $.ajax({
              method:"post",
              data:'selected_customer='+selected_customer+'&id='+cs_id,
              url:"{{ route('add-existing-invoice') }}",
              beforeSend:function(){
               
              },
              success:function(data){
                if(data.search('done') !== -1){
                  window.location.href = "{{ url('sales/get-completed-quotation-products') }}"+"/"+cs_id;
                }
              }
           });
    });

   $('#header_prod_search').keyup(function(event){ 
     
        var query = $(this).val();
        
        var inv_id = $("#quo_id_for_pdf").val();
        if(query != '')
        {

         var _token = $('input[name="_token"]').val();
         $.ajax({
          url:"{{ url('sales/autocomplete-fetch-product') }}",
          method:"POST",
          data:{query:query, _token:_token, inv_id:inv_id},
          success:function(data){
            // console.log(data);
            // $('#header_product_name_div').html(data);
            $('#myId').html(data);
            // document.getElementById('header_product_name_div').innerHTML = data;
          }
         });
        }
        else{
          // alert('hi');
          $('#myId').empty();
          // document.getElementById('#myId').innerHTML = '';
        }

    });

   $('#header_orders_search').keyup(function(){ 
        var query = $(this).val();
        if(query != '')
        {
         var _token = $('input[name="_token"]').val();
         $.ajax({
          url:"{{ route('autocomplete-fetch-orders') }}",
          method:"POST",
          data:{query:query, _token:_token},
          success:function(data){
            // $('#header_orders_div').html(data);
            $('#myId2').html(data);
          }
         });
        }
        else{
          $('#myId2').empty();
        }
    });

});

$('.profileimg').on('click',function(){
      $('.my_click').trigger('click');
      $("#profile").click();
    });
    
    $('#profile').on('change',function(){
      $('#submitbutton').trigger('click');
     
    });

    $("#upload_form").on('submit',function(e){
      e.preventDefault();
       $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
     $.ajax({
        url: "{{ route('update-profile-img') }}",
        method: 'post',
        data: new FormData(this), 
        contentType: false,       
        cache: false,             
        processData:false,
        beforeSend: function(){
          $('#loader_modal').modal('show');
        },
        success: function(result){
        
          if(result.error === false){
            
            toastr.success('Success!', 'Profile image updated successfully',{"positionClass": "toast-bottom-right"});
            $('#upload_form')[0].reset();
            setTimeout(function(){
          $('#loader_modal').modal('hide');

              window.location.reload();
            }, 2000);
            
          }
          
          
        },
        error: function (request, status, error) {
              $('.save-btn').val('add');
              $('.save-btn').removeClass('disabled');
              $('.save-btn').removeAttr('disabled');
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

       // new function for to date which is not  before from 

$('#to_date').change(function(){
  var from = $('#from_date').datepicker('getDate');
  var to = $(this).datepicker('getDate');
  console.log($('#from_date').val());
   if(from !='' && $('#from_date').val() != '')
   {
      if(Date.parse(to)<Date.parse(from))
        {
          toastr.error('Error!', '"End Date" Must be Greater then start Date' ,{"positionClass": "toast-bottom-right"});
           $('#to_date').val('');
        }else{
           $("#apply_filter_btn").val("1");
        }
  }
    
  
});

$('#from_date').change(function(){
  var from = $(this).datepicker('getDate');
  var to = $('#to_date').datepicker('getDate');
    if(to !='' && $('#to_date').val() != '')
    {
      if(Date.parse(to)<Date.parse(from))
       {
        toastr.error('Error!', '"Start Date" Must be less then End Date' ,{"positionClass": "toast-bottom-right"});
         $('#from_date').val('');
        }else{
           $("#apply_filter_btn").val("1");
        }
    }
        
  });

$('.select__user_type').on('change',function(e){
  var id = $(this).val();
  $.ajax({
        method: "get",
        dataType: "json",
        data: {id : id},
        url: "{{ route('superadmin-as-other-user') }}",
        beforeSend: function() {
            $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
            });
            $("#loader_modal").modal('show');
        },
        success: function(data) {
            if(data.success == true)
            {
                if(id == 9)
                {
                    window.location.href = '{{route("ecom-dashboard")}}';
                }
                else if(id == 1)
                {
                    window.location.href = '{{route("sales")}}';
                }
                else if(id == 2)
                {
                    window.location.href = '{{route("purchasing-dashboard")}}';
                }
                else if(id == 3)
                {
                    window.location.href = '{{route("sales")}}';;
                }
                else if(id == 4)
                {
                    window.location.href = '{{route("sales")}}';;
                }
                else if(id == 5)
                {
                    window.location.href = '{{route("importing-receiving-queue")}}';
                }
                else if(id == 6)
                {
                    window.location.href = '{{route("warehouse-dashboard")}}';
                }
                else if(id == 7)
                {
                    window.location.href = '{{route("account-recievable")}}';
                }
                else if(id == 10)
                {
                    window.location.href = '{{route("sales")}}';
                }
                else
                {
                    location.reload();
                }
            }
        }
    });
});

$('.reset, #reset-btn, .reset-btn').on('click',function(){
    $('.entriestable').DataTable().search('').draw();
    });
</script>
<script src="{{asset('public/site/assets/backend/js/bell-notifications.js')}}"></script>
<!-- <script type="text/javascript" src="{{asset('public/site/assets/backend/js/default_date_filter.js')}}"></script> -->
@yield('javascript')

</body>
</html>
