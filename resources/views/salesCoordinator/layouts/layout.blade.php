<!doctype html>
<html lang="en">
<head>
<!-- Required meta tags -->
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="_token" content="{{csrf_token()}}" />
<title>{{$sys_name}}| @yield('title')</title>
@if ($sys_logos->favicon != null)
  <link rel="shortcut icon" href="{{asset('public/uploads/logo/'.$sys_logos->favicon)}}">
@else
  <link rel="shortcut icon" href="{{asset('public/img/logo-icon.png')}}">
@endif
<!-- Bootstrap CSS -->
<link href="{{asset('public/site/assets/backend/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<!-- <link rel="stylesheet" href="{{asset('public/site/assets/backend/css/font-awesome.min.css')}}"> -->
{{--TOOLTIP--}}
<link href="{{asset('public/site/assets/backend/css/qtip.css')}}" type="text/css" rel="stylesheet" />
<!-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css"> -->
<link rel="stylesheet" type="text/css" href="{{asset('public/site/assets/backend/css/jquery.dataTables.min.css')}}">

<!-- <link href="{{ asset('public/'.mix('/css/style.css')) }}" rel="stylesheet"> -->
<link href="{{asset('public/site/assets/backend/css/custom.css?')}}{{ time() }}" rel="stylesheet" type="text/css">
<link href="{{asset('public/site/assets/backend/css/style.css?')}}{{ time() }}" rel="stylesheet" type="text/css">

{{-- Sweet Alert --}}
<link href="{{asset('public/site/assets/backend/css/sweetalert.min.css')}}" rel="stylesheet">
{{-- Toastr Plugin --}}
<link href="{{asset('public/site/assets/backend/css/toastr.min.css')}}" rel="stylesheet">
{{-- JQuery UI --}}
<link href="{{asset('public/site/assets/backend/css/jquery.datetimepicker.min.css')}}" rel="stylesheet">
<link rel="stylesheet" href="{{asset('public/site/assets/backend/css/bootstrap-select.min.css')}}">

{{-- For select2 --}}
<link href="{{asset('public/site/assets/backend/css/select2.min.css')}}" type="text/css" rel="stylesheet" />

{{-- Offline feature css --}}
<link rel="stylesheet" href="{{asset('public/site/assets/offline-language-english.css')}}">
<link rel="stylesheet" href="{{asset('public/site/assets/offline-theme-chrome.css')}}">
@include('layouts.custom_color')
</head>
<body>

<div class="wrapper">
 <input type="hidden" name="get_url" id="get_url" value="{{url('')}}">
 {{-- header code --}}
 @include('salesCoordinator.layouts.header')

<div class="main-content">

 {{-- Left side bar --}}
{{-- @include('salesCoordinator.layouts.left-sidebar') --}}
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

{{-- jquery ui --}}
<!-- <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script> -->
<script src="{{asset('public/site/assets/backend/js/jquery-ui.js')}}"></script>
{{-- Toastr Plugin --}}
<script  src="{{asset('public/site/assets/backend/js/toastr.min.js')}}"></script>
{{-- TOOLTIP--}}
<script  src="{{asset('public/site/assets/backend/js/qtip.js')}}"></script>
<!-- DataTables -->
<script  src="{{asset('public/site/assets/backend/js/jquery.dataTables.js')}}"></script>
<script src="{{asset('public/site/assets/backend/js/jquery.dataTables.ColReorder.js')}}"></script>


<!-- <script src="https://cdn.ckeditor.com/ckeditor5/11.2.0/classic/ckeditor.js"></script> -->
<script src="{{asset('public/site/assets/backend/js/ckeditor.js')}}"></script>

{{-- Sweet Alert --}}
<script  src="{{asset('public/site/assets/backend/js/sweetalert.min.js')}}"></script>

{{-- bootstrap-select --}} 
<script src="{{asset('public/site/assets/backend/js/bootstrap-select.min.js')}}"></script>

{{-- Jquery Timepicker --}}
<script src="{{asset('public/site/assets/backend/js/jquery.datetimepicker.full.min.js')}}"></script>

<script src="{{asset('public/site/assets/backend/js/custom.js')}}"></script>

 {{-- For select2 --}}
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.12/js/select2.full.min.js"></script> -->
<script src="{{asset('public/site/assets/backend/js/select2.full.min.js')}}"></script>

{{-- For offline --}}
<script src="{{asset('public/site/assets/offline.min.js')}}"></script>
<script type="text/javascript" src="{{asset('public/js/offline_script.js')}}"></script>

<script src="{{asset('public/site/assets/jquerysession.js')}}"></script>
<script src="{{asset('public/js/autologout.js')}}"></script>
<script src="{{asset('public/js/custom.js')}}"></script>

<script type="text/javascript">
  $(document).ready(function(){
  $('[data-toggle="tooltip"]').tooltip();
});
 
(jQuery);
</script>

<script>
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
        var position = "header";
        var inv_id = $("#quo_id_for_pdf").val();
        if(query != '')
        {

         var _token = $('input[name="_token"]').val();
         $.ajax({
          url:"{{ url('sales/autocomplete-fetch-product') }}",
          method:"POST",
          data:{query:query, _token:_token, inv_id:inv_id, position:position},
          success:function(data){
            // console.log(data);
            // $('#header_product_name_div').html(data);
            $('#myId').html(data);
            // document.getElementById('header_product_name_div').innerHTML = data;
          }
         });
        }
        else{
          $('#myId').empty();
        }

    });

    document.onclick = function(e){
    var divToHide = document.getElementsByClassName('topsearch-col');
    if(e.target.class !== 'divToHide'){
    $('#myId').empty();
    $('#header_prod_search').val('');
    $('#myId2').empty();
    $('#header_orders_search').val('');
    }
   };

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

   $(document).on('click','#marks-as-read',function(e){
    e.preventDefault();
    // alert($(this).data('id'));
    var id = $(this).data('id');
     $.ajax({
          url:"{{ url('sales/read-mark')}}",
          method:"get",
          data:{id:id},
          success:function(data){
            // $('#header_orders_div').html(data);
            if(data.error == false && data.status == 'read'){
              var hasit = $('.envelope'+id).hasClass('hasit');
              if(!hasit){
                 $('#not'+id).addClass('lightblue');
              $('.userdropdown').addClass('show');
              $('#notifications').addClass('show');
            }else{
               document.getElementById('nott'+id).className = "usercol notinfo gray";
               $('#nott'+id).removeClass('lightgray');
            }
             
              // $('.mark'+id).addClass('d-none');
              $('.envelope'+id).removeClass('fa-envelope');

              $('.envelope'+id).addClass('fa-envelope-open');
            }
            if(data.error == false && data.status == 'unread'){
               var hasit = $('.envelope'+id).hasClass('hasit');
              if(!hasit){
               $('#not'+id).removeClass('lightblue');
              $('.userdropdown').addClass('show');
              $('#notifications').addClass('show');
            }
            else{
               $('#nott'+id).removeClass('gray');

               document.getElementById('nott'+id).className = "usercol notinfo lightgray";
            }
              // $('.mark'+id).addClass('d-none');
              $('.envelope'+id).addClass('fa-envelope');

              $('.envelope'+id).removeClass('fa-envelope-open');
            }
            return false;
            // $('#myId2').html(data);
          }
         });
   });

});
// $(document).ready(function(){


//   // Enable pusher logging - don't include this in production
//     Pusher.logToConsole = true;

//     var pusher = new Pusher('aefb98b84c5257dd1205', {
//       cluster: 'ap2',
//       forceTLS: true
//     });

//     var channel = pusher.subscribe('my-channel');
//     channel.bind('my-event', function(data) {
//       alert(JSON.stringify(data));
//     });

//   });
</script>
  <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script> -->
<script src="https://js.pusher.com/4.3/pusher.min.js"></script>
  <script>

    // Enable pusher logging - don't include this in production
    $(document).ready(function(){
      // Pusher.logToConsole = true;

    var pusher = new Pusher('0dc11ade5d4155ece303', {
      cluster: 'ap2',
      forceTLS: true
    });

    var channel = pusher.subscribe('product-channel');
    channel.bind('my_event', function(data) {
      $.ajax({
          url:"{{ url('sales/get-notifications') }}",
          method:"get",
          // data:{query:query, _token:_token},
          success:function(data){
            $('#notifications').html(data.html);
            $('#badge').html(data.count);
       toastr.success('Alert!', 'You have a new notification.',{"positionClass": "toast-bottom-right"});
          }
         });
      // alert(JSON.stringify(data));
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
  @include('notification_functionality')
  <script type="text/javascript" src="{{asset('public/site/assets/backend/js/default_date_filter.js')}}"></script>
@yield('javascript')

</body>
</html>
