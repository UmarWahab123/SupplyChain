<!doctype html>
<html lang="en">
<head>
<!-- Required meta tags -->
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="_token" content="{{csrf_token()}}" />
<title>{{$sys_name}}</title>
<script src="{{asset('public/site/assets/backend/js/jquery.min.js')}}"></script>

@if ($sys_logos->favicon != null)
  <link rel="shortcut icon" href="{{asset('public/uploads/logo/'.$sys_logos->favicon)}}">
@else
  <link rel="shortcut icon" href="{{asset('public/img/logo-icon.png')}}">
@endif
{{-- DataTables Buttons --}}
<link rel="stylesheet" href="{{asset('public/site/assets/backend/css/buttons.dataTables.min.css')}}">
<!-- Bootstrap CSS -->
<link href="{{asset('public/site/assets/backend/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<!-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css"> -->
<link rel="stylesheet" type="text/css" href="{{asset('public/site/assets/backend/css/jquery.dataTables.min.css')}}">

<!-- <link href="{{ asset('public/'.mix('/css/style.css')) }}" rel="stylesheet"> -->
<link href="{{asset('public/site/assets/backend/css/custom.css?')}}{{ time() }}" rel="stylesheet" type="text/css">
<link href="{{asset('public/site/assets/backend/css/style.css?')}}{{ time() }}" rel="stylesheet" type="text/css">
<link href="{{asset('public/site/assets/backend/css/mobile_header.css?')}}{{ time() }}" rel="stylesheet" type="text/css">

{{-- Sweet Alert --}}
<link href="{{asset('public/site/assets/backend/css/sweetalert.min.css')}}" rel="stylesheet">
{{-- Toastr Plugin --}}
<link href="{{asset('public/site/assets/backend/css/toastr.min.css')}}" rel="stylesheet">
{{-- JQuery UI --}}
<link href="{{asset('public/site/assets/backend/css/jquery.datetimepicker.min.css')}}" rel="stylesheet">
<!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css"> -->
<link rel="stylesheet" href="{{asset('public/site/assets/backend/css/bootstrap-select.min.css')}}">
{{-- For select2 --}}
<link href="{{asset('public/site/assets/backend/css/select2.min.css')}}" type="text/css" rel="stylesheet" />

{{-- JQuery UI Datepicker --}}
<link href="https://cdnjs.cloudflare.com/ajax/libs/datepicker/0.6.5/datepicker.min.css" rel="stylesheet">

{{-- Offline feature css --}}
<link rel="stylesheet" href="{{asset('public/site/assets/offline-language-english.css')}}">
<link rel="stylesheet" href="{{asset('public/site/assets/offline-theme-chrome.css')}}">

{{--Highcharts links--}}
<script src="{{asset('assets/highcharts/code/highcharts.js')}}"></script>
<script src="{{asset('assets/highcharts/code/modules/series-label.js')}}"></script>
<script src="{{asset('assets/highcharts/code/modules/exporting.js')}}"></script>
<script src="{{asset('assets/highcharts/code/modules/export-data.js')}}"></script>
{{-- Datatable ColReorder --}}
<link rel="stylesheet" type="text/css" href="{{asset('public/site/assets/backend/css/dataTables.colReorder.min.css')}}">

<!-- include summernote css/js -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.16/dist/summernote.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>

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
{{--
<style>
    .sidebarbg{
    background:{{$sys_color->system_color}};

  }
  /* .sidebarnav ul .nav-link{
    border-top: 1px solid {{$sys_color->system_color}};
  } */

  .btn, .sidebarin .dropdown-menu, .sidebarnav .dropdown.show .nav-link:hover, .sidebarnav .dropdown.show .nav-link:focus, .sidebarnav li:hover .nav-link, .sidebarnav li:focus .nav-link, .sidebarnav .nav-link:hover, .sidebarnav .nav-link:focus, .sidebarnav .nav-item.active a {
    background-color:{{$sys_color->system_color}};

  }

.sidebarnav .dropdown.show .nav-link:hover, .sidebarnav .dropdown.show .nav-link:focus, .sidebarnav li:hover .nav-link, .sidebarnav li:focus .nav-link, .sidebarnav .nav-link:hover, .sidebarnav .nav-link:focus, .sidebarnav .nav-item.active a {
    border-bottom-color: #175163;
}

.sidebarin .sidebarnav .dropdown-item:hover, .btn-bg:hover, .btn-bg:focus, .btn:hover, .btn:focus, form input[type="submit"]:hover, form input[type="submit"]:focus, form input[type="submit"]:active {
    background: {{$sys_color->system_color}};
    color: #ffffff;
}

.prof-dropdown .username:before {
    background:{{$sys_color->system_color}};
    color: #fff;
}
.paginate_button.current {
    background: {{$sys_color->system_color}} !important;
    color: #ffffff !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button:hover {

    background: {{$sys_color->system_color}} !important;
    color:#ffffff !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button:hover, .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover, .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:active {
    color:#ffffff !important;
}
</style> --}}
@include('layouts.custom_color')

</head>
<body>

<div class="wrapper">
 <input type="hidden" name="get_url" id="get_url" value="{{url('')}}">
 {{-- header code --}}

@if(Auth::user()->role_id == 1 || Auth::user()->role_id == 8 || Auth::user()->role_id == 10 || Auth::user()->role_id == 11)
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
@elseif(Auth::user()->role_id == 7)
  @include('accounting.layouts.header')
  @elseif(Auth::user()->role_id == 9)
  @include('ecom.layouts.header')
@endif


<div class="main-content">

 {{-- Left side bar --}}


@include('general.general_sidebar')

 <!-- Right Content Start Here -->
 <div class="right-content">
 <input type="hidden" id="site_url"  value="{{ url('') }}">

  @yield('content')

 </div>
 <!-- Right Content Ended Here -->


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

<div class="modal fade" id="createTicketModal">
    <div class="modal-dialog modal-lg modal-dialog-centered parcelpop">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div>
        <div class="modal-body ">
          <h3 class="text-capitalize fontmed text-center">New Ticket</h3>
          <div class="mt-3">
            <form id="createTicketForm" enctype="multipart/form-data">
              <div class="form-row">
                <div class="form-group col-12">
                  <input type="text" name="title" class="font-weight-bold form-control-lg ticket-title ticket-title form-control" placeholder="Tilte (Required)" required="">
                </div>
                <div class="form-group col-12">
                  <textarea type="text" name="detail" class="font-weight-bold form-control-lg form-control summernote ticket-description" placeholder="Description (Required)"></textarea>
                </div>
              </div>
              <div class="form-row">
                <div class="form-group col-6 department_html">
                </div>
                <div class="form-group col-6">
                  <input type="file" class="form-control-file ticket-attachments" id="ticket-attachments" name="attachments[]" multiple="multiple" accept=".doc,.docx,.png,.jpg,.jpeg" required>
                  <small>i.e:<span class="text-info"> doc,docx,png,jpg,jpeg</span></small>
                </div>
              </div>
              <div class="form-row">
                <div class="form-group col-12">
                  <input type="url" class="font-weight-bold form-control-lg form-control" name="url" placeholder="Enter Url (Optional)....">
                </div>
              </div>
              <input type="hidden" name="auto_generate" value="0">
              <input type="hidden" name="notification_email" value="{{Auth::user()->email}}">
              <input type="hidden" name="role" value="{{Auth::user()->roles->name}}">
              <input type="hidden" name="role_name" value="{{Auth::user()->name}}">
              @if(Auth::user()->parent)
                <input type="hidden" name="parent_email" value="{{Auth::user()->parent->email}}">
                <input type="hidden" name="parent_role" value="{{Auth::user()->parent->roles->name}}">
              @else
                <input type="hidden" name="parent_email" value="">
                <input type="hidden" name="parent_role" value="">
              @endif
              <div class="form-submit text-center">
                <input type="submit" value="Create" class="btn btn-bg submit-ticket">
                <!-- <input type="reset" value="close" class="btn btn-danger close-btn"> -->
                <input type="reset" value="close" data-dismiss="modal" class="btn btn-danger close-btn">

              </div>
            </form>

          </div>
        </div>
      </div>
    </div>
</div>



<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<!-- <script src="assets/js/jquery-3.3.1.slim.min.js"></script> -->
{{-- jquery ui --}}
<!-- <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script> -->
<script src="{{asset('public/site/assets/backend/js/jquery-ui.js')}}"></script>


<script src="{{asset('public/site/assets/backend/js/popper.min.js')}}"></script>
<script src="{{asset('public/site/assets/backend/js/bootstrap.min.js')}}"></script>
<script src="{{asset('public/site/assets/backend/js/menuscript.js')}}"></script>
<script src="{{asset('public/site/assets/theme-custom.js')}}"></script>


{{-- Toastr Plugin --}}
<script  src="{{asset('public/site/assets/backend/js/toastr.min.js')}}"></script>

<!-- DataTables -->
<script  src="{{asset('public/site/assets/backend/js/jquery.dataTables.js')}}"></script>
<script src="{{asset('public/site/assets/backend/js/jquery.dataTables.ColReorder.js')}}"></script>
<script src="{{asset('public/site/assets/backend/js/datatble-input-pagination.js')}}"></script>



<!-- <script src="https://cdn.ckeditor.com/ckeditor5/11.2.0/classic/ckeditor.js"></script> -->
<script src="{{asset('public/site/assets/backend/js/ckeditor.js')}}"></script>

{{-- Sweet Alert --}}
<script  src="{{asset('public/site/assets/backend/js/sweetalert.min.js')}}"></script>

{{-- bootstrap-select --}}
<script src="{{asset('public/site/assets/backend/js/bootstrap-select.min.js')}}"></script>

{{-- Jquery Timepicker --}}
<script src="{{asset('public/site/assets/backend/js/jquery.datetimepicker.full.min.js')}}"></script>

<script src="{{asset('public/site/assets/backend/js/custom.js')}}"></script>
<script src="{{asset('public/site/assets/jquerysession.js')}}"></script>
 {{-- For select2 --}}
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.12/js/select2.full.min.js"></script> -->
<script src="{{asset('public/site/assets/backend/js/select2.full.min.js')}}"></script>
<script src="{{asset('public/js/autologout.js')}}"></script>
<script src="{{asset('public/js/custom.js')}}"></script>

{{-- For offline --}}
<script src="{{asset('public/site/assets/offline.min.js')}}"></script>
<script type="text/javascript" src="{{asset('public/js/offline_script.js')}}"></script>

 {{-- For Excel Export --}}
 <script  src="{{asset('public/site/assets/backend/js/dataTables.buttons.min.js')}}"></script>
<script  src="{{asset('public/site/assets/backend/js/buttons.colVis.min.js')}}"></script>
 <script  src="{{asset('public/site/assets/backend/js/buttons.flash.min.js')}}"></script>
<script  src="{{asset('public/site/assets/backend/js/jszip.min.js')}}"></script>
<script  src="{{asset('public/site/assets/backend/js/buttons.html5.min.js')}}"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.print.min.js"></script>
{{-- Jquery Datepicker --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/datepicker/0.6.5/datepicker.min.js"></script>


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
    // autologout.js

    $('.close-btn').on('click', function(e){
      $('.modal').modal('hide');
    });

    var activeurl = window.location;
    $('a[href="'+activeurl+'"]').parents('li').addClass('active');
  });

    //Suspend or deactivate customer
    $(document).on('click', '.suspend-user', function(){
      var id = $(this).data('id');
      var role_name = $(this).data('role_name');
      swal({
          title: "Alert!",
          text: "Are you sure you want to suspend this user?",
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
                data:{id:id,type:role_name},
                url:"{{ route('suspend-user') }}",
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
                }
             });
          }
          else{
              swal("Cancelled", "", "error");
          }
     });
    });

    //Activate back user
    $(document).on('click', '.activate-user', function(){
      var id = $(this).data('id');
      var role_name = $(this).data('role_name');
      swal({
          title: "Alert!",
          text: "Are you sure you want to activate this user?",
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
                data:{id:id,type:role_name},
                url:"{{ route('activate-user') }}",
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
                }
             });
          }
          else{
              swal("Cancelled", "", "error");
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
</script>

<script type="text/javascript">
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
        $('#myIddd').empty();
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
</script>
<script type="text/javascript">
    /*ticket model*/
    $(document).on('click', '.create-ticket', function(e) {
      console.log('admin');
        $.ajax({
            method: "get",
            dataType: "json",
            url: "{{ route('ticket-departments') }}",
            beforeSend: function() {
                $('#loader_modal').modal({
                    backdrop: 'static',
                    keyboard: false
                });
                $("#loader_modal").modal('show');
            },
            success: function(data) {
                $('.department_html').html('');
                $("#loader_modal").modal('hide');
                $('.department_html').append(data.html);
                $('.selectpicker').selectpicker('refresh');
                $('#createTicketModal').modal('show');

            }
        });
    });
    $(document).on('submit', '#createTicketForm', function(e) {
        e.preventDefault();
        $('#createTicketModal').modal('hide');
        var x = document.getElementById("ticket-attachments");
        ticket_detail = $('.ticket-description').val();
        var files = x.files;
        var check_file_type = true;
        $.each(files, function(i, file) {
            // ext.push(file.name.split('.').pop().toLowerCase());
            if ($.inArray(file.name.split('.').pop().toLowerCase(), ['doc', 'docx', 'png', 'jpg', 'jpeg']) == -1) {
                check_file_type = false;
            }
        });
        if (check_file_type == true && ticket_detail != '') {
            var token = "{{config('services.ticket.api_key')}}";
            // alert(token);
            var formData = new FormData($(this)[0]);
            var headers = {
                "Authorization": "Bearer " + token,
                "Accept": "application/json",
            }
            proxyurl = "https://cors-anywhere.herokuapp.com/";
            url = "https://support.d11u.com/api/new-ticket";
            // url ="http://localhost:8000/ticketing/api/new-ticket";
             $.ajaxSetup({
              headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
              }
            });
            $.ajax({
                dataType: 'json',

                method: "post",
                // headers: {
                //     "Authorization": "Bearer " + token,
                //     "Accept": "application/json",
                // },
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                // url: url,
                url:"{{ route('create-ticket') }}",
                beforeSend: function() {
                    $('#loader_modal').modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                    $("#loader_modal").modal('show');
                    $('.submit-ticket').attr('disabled', true);
                },
                success: function(data) {
                    // console.log(data);
                    $('.submit-ticket').removeAttr('disabled');
                    if (data.success == true) {
                        // $("#loader_modal").modal('hide');
                        $("#createTicketModal").modal('hide');
                        toastr.success('Success!', 'Ticket Created Successfully!', {
                            "positionClass": "toast-bottom-right"
                        });
                        setTimeout(function() {
                            // window.location.reload();
                            window.location.href = "{{ url('/admin/tickets') }}";
                        }, 2000);
                    }
                    if (data.success == false) {
                        $("#loader_modal").modal('hide');
                        toastr.error('error!', data.message, {
                            "positionClass": "toast-bottom-right"
                        });
                    }
                }
            });
        } else {
            if (ticket_detail == '') {
                toastr.error('Error!', "Please fill ticket detail section", {
                    "positionClass": "toast-bottom-right"
                });
            }
            if (check_file_type == false) {
                toastr.warning('Warning!', "Please select valid file type", {
                    "positionClass": "toast-bottom-right"
                });
            }

        }

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

$('.reset, .reset-btn, #btn_reset').on('click',function(){
    $('.entriestable').DataTable().search('').draw();
    });
</script>
@include('notification_functionality')
<!-- <script type="text/javascript" src="{{asset('public/site/assets/backend/js/default_date_filter.js')}}"></script> -->
@yield('javascript')

</body>
</html>
