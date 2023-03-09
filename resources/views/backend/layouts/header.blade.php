<style type="text/css">
  .modal {
  overflow-y:auto;
}
.badge-custom {
    position: absolute;
    top: 0px;
    right: 9px;
    padding: 5px 8px;
    border-radius: 50%;
    background-color: red;
    color: white;
  }
.unread{
  background-color: #F4F7F7;
}
/*.header-hide{display: none;}*/
</style>
 <!-- New header -->
<header class="header fixed-top bg-white">
  <div class="d-flex toprow position-relative">
      <div class="col-xl-3 col-lg-3 col-md-4 pl-0 d-flex align-items-center col-sm-6">
        <!-- <a href="{{url('admin')}}">
            <img src="{{asset('public/images/static-logo.png')}}" alt="logo" s class="img-fluid pt-1">
        </a> -->
        <figure class="logo position-relative w-100">
                <a href="{{url('admin')}}">
                  @if (($sys_logos->logo != null) && ($sys_logos->small_logo != null))
                    <img class="phone-logo" src="{{asset('public/uploads/logo/'.$sys_logos->logo)}}" alt="logo" style="max-height: 38px; width: auto">
                    <!-- <img src="{{asset('public/uploads/logo/'.$sys_logos->small_logo)}}" alt="logo" class="img-fluid sm-logo"> -->
                  @else
                    <img class="phone-logo" src="{{asset('public/img/logo.png')}}" alt="logo" style="max-height: 38px; width: auto">
                    <!-- <img src="{{asset('public/img/logo-icon.png')}}" alt="logo" class="img-fluid sm-logo"> -->
                  @endif
                  {{-- @if ($sys_logos->logo != null)
                    <img src="{{asset('public/uploads/logo/'.$sys_logos->logo)}}" alt="logo" class="img-fluid lg-logo">
                  @else
                    <img src="{{asset('public/img/logo.png')}}" alt="logo" class="img-fluid lg-logo">
                  @endif
                  @if ($sys_logos->small_logo != null)
                    <img src="{{asset('public/uploads/logo/'.$sys_logos->logo)}}" alt="logo" class="img-fluid sm-logo">
                  @else
                    <img src="{{asset('public/img/logo-icon.png')}}" alt="logo" class="img-fluid sm-logo">
                @endif --}}
                  {{-- <img src="{{asset('public/img/logo.png')}}" alt="logo" class="img-fluid lg-logo">
                  <img class="phone-logo" src="{{asset('public/img/logo-icon.png')}}" alt="logo" class="img-fluid sm-logo"> --}}
                </a>

                <div class="modal" id="loader_modal" role="dialog" style="width: 50%;position: absolute;overflow: hidden;left: 50%;top: 5px;">
                    <p style="text-align:center;"><img class="phone-modal-image" src="{{ asset('public/uploads/gif/waiting.gif') }}"></p>
                </div>
              </figure>
      </div>
      <div class="col hide-desktop_searches topsearch-col p-0 m-1 d-flex align-items-center header__hide_phone">
         <div class="border rounded input-group custom-input-group autosearch">
          <div class="input-group-prepend">
          <span class="input-group-text fa fa-search"></span>
          </div>
          <input type="text" class="form-control pl-1" id="header_prod_search" tabindex="0" name="prod_name" placeholder="Search Products">
          <span id="purchase_loader_product"></span>

          <input class="searchbox form-control pl-1 d-none" id="myInput" type="search" name="mysearches" value="" maxlength="1000" autocapitalize="off" autocomplete="off" placeholder="Search Products">

        </div>
        <p id="myIdd" class="m-0"></p>
      </div>
      <div class="col hide-desktop_searches topsearch-col p-0 m-1 d-flex align-items-center header__hide_phone">
        <div class="border rounded input-group custom-input-group autosearch">
          <div class="input-group-prepend">
          <span class="input-group-text fa fa-search"></span>
          </div>
          <input class="searchbox form-control pl-1 d-none" id="myInput2" type="search" name="mysearches" value="" maxlength="1000" autocapitalize="off" autocomplete="off" placeholder="Search Orders/Quotation">
          <input type="text" class="form-control pl-1" id="header_orders_search" name="header_orders_search" placeholder="Search Orders">
          <span id="purchase_loader_product2"></span>
        </div>
        <p id="myIdd2" class="m-0"></p>
      </div>
      <div class="col hide-desktop_searches topsearch-col p-0 m-1 d-flex align-items-center header__hide_phone">
        <div class="border rounded input-group custom-input-group autosearch">
          <div class="input-group-prepend">
          <span class="input-group-text fa fa-search"></span>
          </div>
          <input class="searchbox form-control pl-1 d-none" id="myInput3" type="search" name="mysearches" value="" maxlength="1000" autocapitalize="off" autocomplete="off" placeholder="Search PO">
          <input type="text" class="form-control pl-1" id="header_po_search" name="header_po_search" placeholder="Search PO">
          <span id="purchase_loader_product3"></span>
        </div>
        <p id="myIdd3" class="m-0"></p>
      </div>
      <div class="col d-flex align-items-center header__hide_phone">
        <div class="input-group-append ml-1 col p-0 pt-1 " style="border-radius: 2px;">
        <a class="btn recived-button custom-recived-button" target="_blank" href="{{route('product-order-invoice')}}" style="display:block;width: 175px;" id="create-quotation" title="Create Quotation"><span>Create Quotation</span></a>
        <!-- <a class="btn recived-button create-ticket ml-1  adv-btn" data-toggle="tooltip" data-placement="auto" data-original-title="Create Ticket">Create Ticket</a> -->
        <a class="btn recived-button custom-create-po-btn ml-1" id="create_po_id" target="_blank" href="{{ route('create-purchase-order-direct') }}" style="display:block;" id="create-quotation" title="Create Purchase Orders">
          <span>Create PO</span></a>

        <a class="btn recived-button custom-create-po-btn ml-1" target="_blank" href="{{ route('create-transfer-document') }}" style="display:block;" id="create_transfer_doc" title="Create Transfer Document">
          <span>Create Transfer</span></a>
          @if(auth()->user()->parent_id != null)
          <div>
            <select class="ml-1 select__user_type" style="height: 39px;">
              <option selected="" disabled>Select User Type</option>
              <option value="1" {{auth()->user()->role_id == '1' ? 'selected' : ''}}>Admin</option>
              <option value="2" {{auth()->user()->role_id == '2' ? 'selected' : ''}}>Purchasing</option>
              <option value="3" {{auth()->user()->role_id == '3' ? 'selected' : ''}}>Sales</option>
              <option value="4" {{auth()->user()->role_id == '4' ? 'selected' : ''}}>Sales Coordinator</option>
              <option value="5" {{auth()->user()->role_id == '5' ? 'selected' : ''}}>Logistics</option>
              <option value="6" {{auth()->user()->role_id == '6' ? 'selected' : ''}}>Warehouse</option>
              <option value="7" {{auth()->user()->role_id == '7' ? 'selected' : ''}}>Accounting</option>
              <option value="9" {{auth()->user()->role_id == '9' ? 'selected' : ''}}>Ecommerce Manager</option>
              <option value="10" {{auth()->user()->role_id == '10' ? 'selected' : ''}}>Super Admin</option>
              <option value="11" {{auth()->user()->role_id == '11' ? 'selected' : ''}}>Manager</option>
            </select>
          </div>
          @endif

      </div>
      </div>


  <!-- <div class="top-bar p-0">
  <div class="align-items-center ml-0 mr-0 row"> -->

  <!-- <div class="col-xl-1 col-lg-1 col-md-1 col-sm-1 p-0 topsearches pr-5 headings-color top-notify"></div> -->

 <!--  <div class="col-xl-9 col-lg-7 col-md-8 col-sm-7 topsearches pr-5">
    <div class="d-flex pl-3"> -->








      <!-- <a class="btn btn-sm create-ticket pr-2 pl-2 adv-btn align-items-center d-flex justify-content-center" data-toggle="tooltip" data-placement="auto" data-original-title="Create Ticket"><img src="{{ asset('public/menu-icon/pencil-30.png') }}"></a> -->
      <!-- <button class="btn btn-sm create-ticket  adv-btn " id="create-ticket" title="Create Ticket" ><img src="{{ asset('public/menu-icon/pencil-30.png') }}"></button> -->
    <!-- </div>
  </div> -->


  <!-- Header User Info Start Here -->
  <div class="col-xl-2 col-lg-4 col-md-8 col-sm-6 userlinks pr-4  d-flex justify-content-end">

          <!-- Bell Notifications -->
<div class="d-flex align-items-center">
   <ul class="align-items-center justify-content-end d-flex list-unstyled mb-0">
      <li class="nav-item dropdown prof-dropdown d-flex">
        <a class="align-items-center d-flex dropdown-toggle nav-link my_click remove-after" href="#"data-toggle="dropdown">
          <img class="mobile-bell-icon" src="{{asset('public/img/bell.png')}}" style="height: 35px; width: 35px">
            @if($dummy_data != null && $dummy_data->where('read_at', null)->count() != 0)
            <span class="badge badge-custom">
              {{$dummy_data->where('read_at', null)->count()}}
            </span>
            @endif
        </a>
        <div class="dropdown-menu bell-dropdown mt-2" style="box-shadow: 0px 0px 4px #a9a9a9; max-height: 500px; top: -5px !important; width:300px; height: auto; @if($dummy_data != null && $dummy_data->count() >= 8) overflow-y:scroll; @endif">
          @if($dummy_data != null && $dummy_data->count() > 0)
          @foreach($dummy_data as $data)
            <div class="row m-1 @if($data->read_at == null) unread @endif bell-row" style="border: 1px solid black" id="grand-parent-div-{{$data->id}}">
              <div class="col-md-11 mt-2 mb-2 mr-2 bell-title">
                @php
                $column_data = json_decode($data->data);
                @endphp
                <h6>{{$column_data->subject}}</h6>
                <span>{{strip_tags($column_data->body)}}</span>
              </div>
              @if($data->read_at == null)
              <div class="mt-2 mb-2 mr-0 bell-cross-btn" id="parent-div-{{$data->id}}">
                <a href="#" class="cross-btn" title="Clear" data-id = "{{$data->id}}"><img class="mobile-cross-btn" src="{{asset('public/img/cross.png')}}" width="10px" height="10px"></a>
              </div>
              @endif
            </div>
          @endforeach
          @if($dummy_data != null && $dummy_data->where('read_at', null)->count() > 0)
          <a href="#" class="float-right btn recived-button m-2 text-center clear-all" style="width: 110px" title="Clear All">Clear All</a>
          @endif
          @else
            <div class="text-center">No Notification ! </div>
          @endif

        </div>
      </li>
   </ul>
</div>

    <ul class="align-items-center justify-content-end d-flex list-unstyled mb-0 userlist">
      <li class="nav-item dropdown prof-dropdown d-flex">
      <div class="dropdown-menu dropdown-menu-right admin-notification">
        <div class="usercol notinfo">
          <a href="#" class="notiflink">
            <div class="notifi-name fontbold">Jessica Caruso</div>
            <div class="notifi-desc">accepted your invitation to join the team.</div>
            <span class="notifi-date fontmed">2 min ago</span>
          </a>
         </div>

         <div class="usercol notinfo">
            <a href="#" class="notiflink">
              <div class="notifi-name fontbold">Jessica Caruso</div>
              <div class="notifi-desc">accepted your invitation to join the team.</div>
              <span class="notifi-date fontmed">2 min ago</span>
            </a>
         </div>

         <div class="usercol notinfo">
            <a href="#" class="notiflink">
              <div class="notifi-name fontmed text-center">View More</div>
              <!-- <div class="notifi-desc">accepted your invitation to join the team.</div> -->
              <!-- <span class="notifi-date fontmed">2 min ago</span> -->
            </a>
         </div>
      </div>
    </li>
    <li class="nav-item dropdown prof-dropdown d-flex">
      <a class="align-items-center d-flex dropdown-toggle nav-link profilelink my_click" href="#" id="profilelink" data-toggle="dropdown">

       @if(@Auth::user()->user_details->image != null && file_exists( public_path() . '/uploads/admin/images/' . @Auth::user()->user_details->image))
        <img src="{{asset('public/uploads/admin/images/'.@Auth::user()->user_details->image)}}" alt="user image" class="img-fluid rounded-circle img-fluid profileimg mobile-profileimg"><i class="fa fa-camera h-camera" style="
    display: none;
"></i>
       @else
        <img src="{{asset('public/img/default-profile-image.png')}}" alt="user image" class="img-fluid rounded-circle img-fluid profileimg mobile-profileimg"><i class="fa fa-camera h-camera" style="
    display: none;
"></i>
        @endif
        <span class="username text-center mobile-username"><b>{{@Auth::user()->roles->name}} :</b> {{Auth::user()->name}}</span>
      </a>
       <div style="display: none;">
        <form enctype="multipart/form-data" method="post" id="upload_form">

      <input type="file" name="profileimg" onchange="validateImage()" id="profile" style="">
       <button id="submitbutton" type="submit">Upload</button>
      </form>
      </div>
      <div class="dropdown-menu mt-0" style="margin-right: 15px;">
          {{--<a class="dropdown-item" href="{{url('admin/admin-profile-setting')}}">
            <span class="dropdown-icon oi oi-person"></span> Profile
          </a>--}}

          @if($current_version)
          <a class="dropdown-item" href="{{route('version')}}">
            <span class="dropdown-icon oi oi-person"></span> Version {{$current_version}}
          </a>
          @endif
          <a class="dropdown-item" href="{{route('change-password-admin')}}">
          <span class="dropdown-icon oi oi-person"></span> Change Password
        </a>
          <a class="dropdown-item logoutCheck" href="{{ route('logout') }}"  onclick="event.preventDefault();$('#if_session_expire').val('true');document.getElementById('logout-form').submit();">
          <span class="dropdown-icon oi oi-account-logout"></span> {{ __('Logout') }}
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
        <input id="if_session_expire" name="if_session_expire" type="hidden" value="false">
        </form>

            <a class="dropdown-item d-none" href="#">
              <span class="dropdown-icon oi oi-account-logout"></span> Logout
            </a>
          <div class="dropdown-divider d-none"></div>
          <a class="dropdown-item d-none" href="#">Help Center</a>
          <a class="dropdown-item d-none" href="#">Ask Forum</a>
          <a class="dropdown-item d-none" href="#">Keyboard Shortcuts</a>
        </div>
    </li>
  </ul>
</div>
  <!-- Header User Info End Here -->
 <!--  </div>
</div> -->
</div>
</header>

<!-- Loader Modal -->
<!-- <div class="modal" id="loader_modal_old" role="dialog">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-body">
        <h3 style="text-align:center;">Please wait</h3>
        <p style="text-align:center;"><img src="{{ asset('public/uploads/gif/waiting.gif') }}"></p>
      </div>
    </div>
  </div>
</div> -->
@include('backend.layouts.session-expire')
<!-- <script type="text/javascript">
  $(window).scroll(function(e) {

    // add/remove class to navbar when scrolling to hide/show
    var scroll = $(window).scrollTop();
    if (scroll >= 100) {
        $('.header').addClass("header-hide");
    } else {
        $('.header').removeClass("header-hide");
    }

});
</script> -->


<script type="text/javascript">
  function validateImage() {
  console.log("validateImage called");
  var formData = new FormData();

  var file = document.getElementById("profile").files[0];

  formData.append("Filedata", file);
  var t = file.type.split('/').pop().toLowerCase();
  if (t != "jpeg" && t != "jpg" && t != "png" && t != "bmp" && t != "svg") {
    toastr.error('Uploaded file image must be jpeg, jpg, bmp, png or svg.','Error!' , {
                                "positionClass": "toast-bottom-right"
                            });
    document.getElementById("profile").value = '';
    return false;
  }
  if (file.size > 2048000) {
    toastr.error('Image size must be less than 2MB.','Error!' , {
                                "positionClass": "toast-bottom-right"
                            });
    document.getElementById("profile").value = '';
    return false;
  }
  return true;
}

// $(document).on('click', '.bell-dropdown', function (e) {
//   e.stopPropagation();
// })


// $('.cross-btn').click(function(e) {
//   e.stopPropagation();
//   $(this).parent().parent().removeClass('unread');
//   $(this).addClass('d-none');
//   var counter = $('.badge').html();
//   counter = counter-1;
//   if(counter != 0){
//     $('.badge').html(counter);
//   }
//   else
//   {
//     $('.badge').html('');
//   }
// });

// $('.clear-all').click(function(e) {
//   e.stopPropagation();
//   $('.unread').removeClass('unread');
//   $('.cross-btn').addClass('d-none');
//   $('.badge').html('');
// });

</script>


