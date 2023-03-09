<!doctype html>
<html lang="en">
<head>
<!-- Required meta tags -->
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="_token" content="{{csrf_token()}}" />
<title>{{$sys_name}} | @yield('title')</title>
@if ($sys_logos->favicon != null)
  <link rel="shortcut icon" href="{{asset('public/uploads/logo/'.$sys_logos->favicon)}}">
@else
  <link rel="shortcut icon" href="{{asset('public/img/logo-icon.png')}}">
@endif

<!-- Bootstrap CSS -->
<link href="{{asset('public/site/assets/backend/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css">
{{-- DataTables Buttons --}}
<link rel="stylesheet" href="{{asset('public/site/assets/backend/css/buttons.dataTables.min.css')}}">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<link rel="stylesheet" type="text/css" href="{{asset('public/site/assets/backend/css/jquery.dataTables.min.css')}}">
<!-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css"> -->

<!-- <link href="{{ asset('public/'.mix('/css/style.css')) }}" rel="stylesheet"> -->
<link href="{{asset('public/site/assets/backend/css/custom.css?')}}{{ time() }}" rel="stylesheet" type="text/css">
<link href="{{asset('public/site/assets/backend/css/style.css?')}}{{ time() }}" rel="stylesheet" type="text/css">
<link href="{{asset('public/site/assets/backend/css/mobile_header.css?')}}{{ time() }}" rel="stylesheet" type="text/css">

{{-- Sweet Alert --}}
<link href="{{asset('public/site/assets/backend/css/sweetalert.min.css')}}" rel="stylesheet">
{{-- Toastr Plugin --}}
<link href="{{asset('public/site/assets/backend/css/toastr.min.css')}}" rel="stylesheet">
{{-- JQuery UI Datepicker --}}
<link href="https://cdnjs.cloudflare.com/ajax/libs/datepicker/0.6.5/datepicker.min.css" rel="stylesheet">
{{-- For select2 --}}
<link href="{{asset('public/site/assets/backend/css/select2.min.css')}}" type="text/css" rel="stylesheet" />


<link rel="stylesheet" href="{{asset('public/site/assets/backend/css/bootstrap-select.min.css')}}">
<!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css"> -->

{{-- Offline feature css --}}
<link rel="stylesheet" href="{{asset('public/site/assets/offline-language-english.css')}}">
<link rel="stylesheet" href="{{asset('public/site/assets/offline-theme-chrome.css')}}">
{{-- Datatable ColReorder --}}
<link rel="stylesheet" type="text/css" href="{{asset('public/site/assets/backend/css/dataTables.colReorder.min.css')}}">

{{--TOOLTIP--}}
<link href="{{asset('public/site/assets/backend/css/qtip.css')}}" type="text/css" rel="stylesheet" />
<style type="text/css">
  .search_product:focus{background-color: #eee;}
</style>

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
@if(Auth::user()->role_id == 1 || Auth::user()->role_id == 8  || Auth::user()->role_id == 10 || Auth::user()->role_id == 11)
  @include('backend.layouts.header')
@elseif(Auth::user()->role_id == 2)
  @include('users.layouts.header')
@elseif(Auth::user()->role_id == 3)
  @include('sales.layouts.header')
@elseif(Auth::user()->role_id == 4)
  @include('sales.layouts.header')
@elseif(Auth::user()->role_id == 5)
  @include('importing.layouts.header')
@elseif(Auth::user()->role_id == 6)
  @include('warehouse.layouts.header')
@endif

<div class="main-content">

{{-- Left side bar --}}
{{--
@if(Auth::user()->role_id == 1)
  @include('backend.layouts.left-sidebar')
@elseif(Auth::user()->role_id == 2)
  @include('users.layouts.left-sidebar')
@elseif(Auth::user()->role_id == 3)
  @include('sales.layouts.left-sidebar')
@elseif(Auth::user()->role_id == 4)
  @include('sales.layouts.left-sidebar')
@elseif(Auth::user()->role_id == 5)
  @include('importing.layouts.left-sidebar')
@elseif(Auth::user()->role_id == 6)
 @include('warehouse.layouts.left-sidebar')
@endif
--}}

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
<script src="{{asset('public/site/assets/backend/js/jquery-ui.js')}}"></script>
<!-- <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script> -->
{{-- Toastr Plugin --}}
<script  src="{{asset('public/site/assets/backend/js/toastr.min.js')}}"></script>
{{-- TOOLTIP--}}
<script  src="{{asset('public/site/assets/backend/js/qtip.js')}}"></script>

<!-- DataTables -->
<script  src="{{asset('public/site/assets/backend/js/jquery.dataTables.js')}}"></script>
<script src="{{asset('public/site/assets/backend/js/jquery.dataTables.ColReorder.js')}}"></script>


<script src="{{asset('public/site/assets/backend/js/ckeditor.js')}}"></script>
<!-- <script src="https://cdn.ckeditor.com/ckeditor5/11.2.0/classic/ckeditor.js"></script> -->

{{-- For select2 --}}
<script src="{{asset('public/site/assets/backend/js/select2.full.min.js')}}"></script>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.12/js/select2.full.min.js"></script> -->

{{-- Sweet Alert --}}
<script  src="{{asset('public/site/assets/backend/js/sweetalert.min.js')}}"></script>

{{-- bootstrap-select --}}
<script src="{{asset('public/site/assets/backend/js/bootstrap-select.min.js')}}"></script>

{{-- Jquery Datepicker --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/datepicker/0.6.5/datepicker.min.js"></script>

{{-- Datatable Buttons --}}
<script  src="{{asset('public/site/assets/backend/js/dataTables.buttons.min.js')}}"></script>

{{-- Datatable Col Visibility --}}
<script src="{{ asset('public/site/assets/backend/js/buttons.colVis.min.js') }}"></script>

<script src="{{asset('public/site/assets/backend/js/datatble-input-pagination.js')}}"></script>


{{-- For offline --}}
<script src="{{asset('public/site/assets/offline.min.js')}}"></script>
<script type="text/javascript" src="{{asset('public/js/offline_script.js')}}"></script>

<script src="{{asset('public/site/assets/backend/js/custom.js')}}"></script>

<script src="{{asset('public/site/assets/jquerysession.js')}}"></script>
<script src="{{asset('public/js/autologout.js')}}"></script>
<script src="{{asset('public/js/custom.js')}}"></script>
    <script src="{{asset('public/js/html2pdf.min.js')}}"></script>

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
                                if(getsession != null && getsession != '')
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
                                var getsession = window.sessionStorage.getItem(new_name);
                                if(getsession != null && getsession != '')
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

            });


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
    var user = "{{Auth::user()->id}}";

    $.fn.dataTable.ext.errMode = 'throw';
    var activeurl = window.location;
    $('a[href="'+activeurl+'"]').parents('li').addClass('active');

    $('.close-btn').on('click', function(e){
      $('.modal').modal('hide');
    });
    $('.add_product_to search_product').mousedown(function(e){
      alert('hi');
    })

    var keyindex, alinks;
    keyindex = -1;

    $('#header_prod_search').keyup(function(event){
      keyindex = -1;
      alinks = '';
      var query = $(this).val();
      var inv_id = $("#quo_id_for_pdf").val();

      if(event.keyCode == 13)
      {
        if(query.length > 2)
        {
          var _token = $('input[name="_token"]').val();
          $.ajax({
            url:"{{ route('purchase-fetch-product') }}",
            method:"POST",
            data:{query:query, _token:_token, inv_id:inv_id},
            beforeSend: function(){
              $('#purchase_loader_product').html('<div align="center"><img src="{{asset("public/img/spinner.gif")}}" height="35"></div>');
            },
            success:function(data){
              $('#purchase_loader_product').empty();
              $('#myIdd').html(data);

              alinks = $('#myIdd').find('a');
              if (alinks.length === 0)
              {
                keyindex = -1;
              }
              if (event.keyCode == 40)
              {
                event.preventDefault();
                if (alinks.length > 0 && keyindex == -1)
                {
                  keyindex = 0;
                  $('#myIdd').find('a')[keyindex++].focus();
                  var dat = $('#myIdd').find('a')[keyindex-1].text;
                  document.getElementById('header_prod_search').value = dat;
                  // alert(dat);
                }
              }
             }
          });
        }
        else
        {
          $('#myIdd').empty();
          toastr.error('Error!', 'Please enter atlesat 3 characters then press Enter !!!' ,{"positionClass": "toast-bottom-right"});
        }
      }

    });

    $('#myIdd').keydown(function(e) {
      alinks = $('#myIdd').find('a');
      if (e.keyCode == 40)
      {
        e.preventDefault();
        if (keyindex == -1)
        {
          keyindex = 1;
        }
        if (alinks.length > 0 && keyindex < alinks.length)
        {
          $('#myIdd').find('a')[keyindex++].focus();
          var dat = $('#myIdd').find('a')[keyindex-1].text;
          document.getElementById('header_prod_search').value = dat;
          // alert(dat);
        }
      }
      if (e.keyCode == 38)
      {
        e.preventDefault();
        if (keyindex == alinks.length)
        {
          keyindex = keyindex - 2;
        }

        if (alinks.length > 0 && keyindex < alinks.length && keyindex >= 0)
        {
          if(keyindex == 0)
          {
            document.getElementById('header_prod_search').value = '';
            document.getElementById('header_prod_search').focus();
          }
          else
          {
            $('#myIdd').find('a')[--keyindex].focus();
            var dat = $('#myIdd').find('a')[keyindex].text;
            document.getElementById('header_prod_search').value = dat;
          }

        }
      }
    });

    $('#header_prod_search_mobile').keyup(function(event) {
                    keyindex = -1;
                    alinks = '';
                    var query = $(this).val();
                    var inv_id = $("#quo_id_for_pdf").val();

                    if (event.keyCode == 13) {
                        if (query.length > 2) {
                            var _token = $('input[name="_token"]').val();
                            $.ajax({
                                url: "{{ route('purchase-fetch-product') }}",
                                method: "POST",
                                data: {
                                    query: query,
                                    _token: _token,
                                    inv_id: inv_id
                                },
                                beforeSend: function() {
                                    $('#purchase_loader_product_mobile').html('<div align="center"><img src="{{asset("public/img/spinner.gif")}}" height="35"></div>');
                                },
                                success: function(data) {
                                    $('#purchase_loader_product_mobile').empty();
                                    $('#myIdd_mobile').html(data);

                                    alinks = $('#myIdd_mobile').find('a');
                                    if (alinks.length === 0) {
                                        keyindex = -1;
                                    }
                                    if (event.keyCode == 40) {
                                        event.preventDefault();
                                        if (alinks.length > 0 && keyindex == -1) {
                                            keyindex = 0;
                                            $('#myIdd').find('a')[keyindex++].focus();
                                            var dat = $('#myIdd_mobile').find('a')[keyindex - 1].text;
                                            document.getElementById('header_prod_search_mobile').value = dat;
                                            // alert(dat);
                                        }
                                    }
                                }
                            });
                        } else {
                            $('#myIdd_mobile').empty();
                            toastr.error('Error!', 'Please enter atlesat 3 characters then press Enter !!!', {
                                "positionClass": "toast-bottom-right"
                            });
                        }
                    }

    });

    $('#myIdd_mobile').keydown(function(e) {
        alinks = $('#myIdd_mobile').find('a');
        if (e.keyCode == 40) {
            e.preventDefault();
            if (keyindex == -1) {
                keyindex = 1;
            }
            if (alinks.length > 0 && keyindex < alinks.length) {
                $('#myIdd').find('a')[keyindex++].focus();
                var dat = $('#myIdd_mobile').find('a')[keyindex - 1].text;
                document.getElementById('header_prod_search_mobile').value = dat;
                // alert(dat);
            }
        }
        if (e.keyCode == 38) {
            e.preventDefault();
            if (keyindex == alinks.length) {
                keyindex = keyindex - 2;
            }

            if (alinks.length > 0 && keyindex < alinks.length && keyindex >= 0) {
                if (keyindex == 0) {
                    document.getElementById('header_prod_search_mobile').value = '';
                    document.getElementById('header_prod_search_mobile').focus();
                } else {
                    $('#myIdd_mobile').find('a')[--keyindex].focus();
                    var dat = $('#myIdd_mobile').find('a')[keyindex].text;
                    document.getElementById('header_prod_search_mobile').value = dat;
                }

            }
        }
    });

    document.onclick = function(e){
      var divToHide = document.getElementsByClassName('topsearch-col');
      if(e.target.class !== 'divToHide')
      {
        $('#myIdd').empty();
        $('#header_prod_search').val('');
        $('#myIdd2').empty();
        $('#header_orders_search').val('');
        $('#myIdd3').empty();
        $('#header_po_search').val('');

        //mobile devices
        $('#myIdd_mobile').empty();
        $('#header_prod_search_mobile').val('');
        $('#myIdd2_mobile').empty();
        $('#header_orders_search_mobile').val('');
        $('#myIdd3_mobile').empty();
        $('#header_po_search_mobile').val('');
      }
    };

  });

  var keyindex1, alinks1;
  keyindex1 = -1;
  $('#header_orders_search').keyup(function(event){

    keyindex1 = -1;
    alinks1 = '';
    var query = $(this).val();
    if(event.keyCode == 13)
    {
      if(query.length > 2)
      {
        var _token = $('input[name="_token"]').val();
        $.ajax({
          url:"{{ route('purchase-fetch-orders') }}",
          method:"POST",
          data:{query:query, _token:_token},
          beforeSend: function(){
            $('#purchase_loader_product2').html('<div align="center"><img src="{{asset("public/img/spinner.gif")}}" height="35"></div>');
          },
          success:function(data){
            $('#purchase_loader_product2').empty();
            $('#myIdd2').html(data);
            alinks1 = $('#myIdd2').find('a');
            if (alinks1.length === 0)
            {
              keyindex1 = -1;
            }
            if (event.keyCode == 40)
            {
              event.preventDefault();
              if (alinks1.length > 0 && keyindex1 == -1)
              {
                keyindex1 = 0;
                $('#myIdd2').find('a')[keyindex1++].focus();
              }
            }
          }
         });
      }
      else
      {
        $('#myIdd2').empty();
        toastr.error('Error!', 'Please enter atlesat 3 characters then press Enter !!!' ,{"positionClass": "toast-bottom-right"});
      }
    }
  });

    $('#myIdd2').keydown(function(e) {
      alinks1 = $('#myIdd2').find('a');
      if (e.keyCode == 40)
      {
        e.preventDefault();
        if (keyindex1 == -1)
        {
          keyindex1 = 1;
        }
        if (alinks1.length > 0 && keyindex1 < alinks1.length)
        {
          $('#myIdd2').find('a')[keyindex1++].focus();
        }
      }
      if (e.keyCode == 38) {
        e.preventDefault();
        if (keyindex1 == alinks1.length)
        {
          keyindex1 = keyindex1 - 2;
        }
        if (alinks1.length > 0 && keyindex1 < alinks1.length && keyindex1 >= 0)
        {
          $('#myIdd2').find('a')[keyindex1--].focus();
        }
      }
    });

    $('#header_orders_search_mobile').keyup(function(event) {

                keyindex1 = -1;
                alinks1 = '';
                var query = $(this).val();
                if (event.keyCode == 13) {
                    if (query.length > 2) {
                        var _token = $('input[name="_token"]').val();
                        $.ajax({
                            url: "{{ route('purchase-fetch-orders') }}",
                            method: "POST",
                            data: {
                                query: query,
                                _token: _token
                            },
                            beforeSend: function() {
                                $('#purchase_loader_product2_mobile').html('<div align="center"><img src="{{asset("public/img/spinner.gif")}}" height="35"></div>');
                            },
                            success: function(data) {
                                $('#purchase_loader_product2_mobile').empty();
                                $('#myIdd2_mobile').html(data);
                                alinks1 = $('#myIdd2_mobile').find('a');
                                if (alinks1.length === 0) {
                                    keyindex1 = -1;
                                }
                                if (event.keyCode == 40) {
                                    event.preventDefault();
                                    if (alinks1.length > 0 && keyindex1 == -1) {
                                        keyindex1 = 0;
                                        $('#myIdd2_mobile').find('a')[keyindex1++].focus();
                                    }
                                }
                            }
                        });
                    } else {
                        $('#myIdd2_mobile').empty();
                        toastr.error('Error!', 'Please enter atlesat 3 characters then press Enter !!!', {
                            "positionClass": "toast-bottom-right"
                        });
                    }
                }
    });

    $('#myIdd2_mobile').keydown(function(e) {
        alinks1 = $('#myIdd2_mobile').find('a');
        if (e.keyCode == 40) {
            e.preventDefault();
            if (keyindex1 == -1) {
                keyindex1 = 1;
            }
            if (alinks1.length > 0 && keyindex1 < alinks1.length) {
                $('#myIdd2_mobile').find('a')[keyindex1++].focus();
            }
        }
        if (e.keyCode == 38) {
            e.preventDefault();
            if (keyindex1 == alinks1.length) {
                keyindex1 = keyindex1 - 2;
            }
            if (alinks1.length > 0 && keyindex1 < alinks1.length && keyindex1 >= 0) {
                $('#myIdd2_mobile').find('a')[keyindex1--].focus();
            }
        }
    });

    var keyindex2, alinks2;
    keyindex2 = -1;
    $('#header_po_search').keyup(function(event){
      keyindex2 = -1;
      alinks2 = '';
      var query = $(this).val();
      if(event.keyCode == 13)
      {
        if(query.length > 2)
        {
          var _token = $('input[name="_token"]').val();
          $.ajax({
          url:"{{ route('purchase-fetch-purchase-orders') }}",
          method:"POST",
          data:{query:query, _token:_token},
          beforeSend: function(){
            $('#purchase_loader_product3').html('<div align="center"><img src="{{asset("public/img/spinner.gif")}}" height="35"></div>');
          },
          success:function(data){
            $('#purchase_loader_product3').empty();
            $('#myIdd3').html(data);
            alinks2 = $('#myIdd3').find('a');
            if (alinks2.length === 0)
            {
              keyindex2 = -1;
            }
            if (event.keyCode == 40)
            {
              event.preventDefault();
              if (alinks2.length > 0 && keyindex2 == -1)
              {
                keyindex2 = 0;
                $('#myIdd3').find('a')[keyindex2++].focus();
              }
            }
          }
         });
        }
        else
        {
          $('#myIdd3').empty();
          toastr.error('Error!', 'Please enter atlesat 3 characters then press Enter !!!' ,{"positionClass": "toast-bottom-right"});
        }
      }
    });

    $('#myIdd3').keydown(function(e) {
      alinks2 = $('#myIdd3').find('a');
      if (e.keyCode == 40)
      {
        e.preventDefault();
        if (keyindex2 == -1)
        {
          keyindex2 = 1;
        }
        if (alinks2.length > 0 && keyindex2 < alinks2.length)
        {
          $('#myIdd3').find('a')[keyindex2++].focus();
        }
      }
      if(e.keyCode == 38)
      {
        e.preventDefault();
        if (keyindex2 == alinks2.length)
        {
          keyindex2 = keyindex2 - 2;
        }

        if (alinks2.length > 0 && keyindex2 < alinks2.length && keyindex2 >= 0)
        {
          $('#myIdd3').find('a')[keyindex2--].focus();
        }
      }
    });

    $('#header_po_search_mobile').keyup(function(event) {
                keyindex2 = -1;
                alinks2 = '';
                var query = $(this).val();
                if (event.keyCode == 13) {
                    if (query.length > 2) {
                        var _token = $('input[name="_token"]').val();
                        $.ajax({
                            url: "{{ route('purchase-fetch-purchase-orders') }}",
                            method: "POST",
                            data: {
                                query: query,
                                _token: _token
                            },
                            beforeSend: function() {
                                $('#purchase_loader_product3_mobile').html('<div align="center"><img src="{{asset("public/img/spinner.gif")}}" height="35"></div>');
                            },
                            success: function(data) {
                                $('#purchase_loader_product3_mobile').empty();
                                $('#myIdd3_mobile').html(data);
                                alinks2 = $('#myIdd3_mobile').find('a');
                                if (alinks2.length === 0) {
                                    keyindex2 = -1;
                                }
                                if (event.keyCode == 40) {
                                    event.preventDefault();
                                    if (alinks2.length > 0 && keyindex2 == -1) {
                                        keyindex2 = 0;
                                        $('#myIdd3_mobile').find('a')[keyindex2++].focus();
                                    }
                                }
                            }
                        });
                    } else {
                        $('#myIdd3').empty();
                        toastr.error('Error!', 'Please enter atlesat 3 characters then press Enter !!!', {
                            "positionClass": "toast-bottom-right"
                        });
                    }
                }
    });

    $('#myIdd3_mobile').keydown(function(e) {
        alinks2 = $('#myIdd3_mobile').find('a');
        if (e.keyCode == 40) {
            e.preventDefault();
            if (keyindex2 == -1) {
                keyindex2 = 1;
            }
            if (alinks2.length > 0 && keyindex2 < alinks2.length) {
                $('#myIdd3_mobile').find('a')[keyindex2++].focus();
            }
        }
        if (e.keyCode == 38) {
            e.preventDefault();
            if (keyindex2 == alinks2.length) {
                keyindex2 = keyindex2 - 2;
            }

            if (alinks2.length > 0 && keyindex2 < alinks2.length && keyindex2 >= 0) {
                $('#myIdd3_mobile').find('a')[keyindex2--].focus();
            }
        }
    });

    $('.profileimg').on('click',function(){
      $('.my_click').trigger('click');
      $("#profile").click();
    });

    $('#profile').on('change',function(){
        var test = validateImage();  // this will grab you the return value from validateImage();
        if(test){
            $('#submitbutton').trigger('click');
        }else{
            return false;
        }
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
           if (result.error) {
                            toastr.error((result.error),"Must be Upload image and maximum image size 2MB", {
                                "positionClass": "toast-bottom-right"
                            });
                            $('#loader_modal').modal('hide');
                        }

                         if (result.success === 1) {

                          toastr.success('Success!', 'Profile image updated successfully', {
                                "positionClass": "toast-bottom-right"
                            });
                            $('#upload_form')[0].reset();
                            setTimeout(function() {
                                $('#loader_modal').modal('hide');
                                window.location.reload();
                            }, 2000);
                        }
          // if(result.error === false)
          // {
          //   toastr.success('Success!', 'Profile image updated successfully',{"positionClass": "toast-bottom-right"});
          //   $('#upload_form')[0].reset();
          //   setTimeout(function(){
          //     $('#loader_modal').modal('hide');
          //     window.location.reload();
          //   }, 2000);
          // }
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
@include('notification_functionality')
<!-- <script type="text/javascript" src="{{asset('public/site/assets/backend/js/default_date_filter.js')}}"></script> -->
@yield('javascript')

</body>
</html>
