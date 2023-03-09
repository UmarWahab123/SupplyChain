
<style type="text/css">
  .modal {
  overflow-y:auto;
}
</style>
<header class="header fixed-top bg-white">
<div class="d-flex toprow position-relative">
    <div class="pt-2 col-xl-2 col-lg-2 col-md-4 col-sm-6 pl-0 d-flex">
      <!-- <a href="{{url('/')}}">
            <img src="{{asset('public/images/static-logo.png')}}" alt="logo" class="img-fluid pt-1">
      </a> -->

 <figure class="logo ml-2 w-100 position-relative">
      <a href="{{url('/')}}">
        @if (($sys_logos->logo != null) && ($sys_logos->small_logo != null))
              <img class="phone-logo" src="{{asset('public/uploads/logo/'.$sys_logos->logo)}}" alt="logo" style="max-height: 38px; width: auto">
              <!-- <img src="{{asset('public/uploads/logo/'.$sys_logos->small_logo)}}" alt="logo" class="img-fluid sm-logo"> -->
            @else
              <img class="phone-logo" src="{{asset('public/img/logo.png')}}" alt="logo" style="height: auto; width: 150px">
          <!-- <img src="{{asset('public/img/logo-icon.png')}}" alt="logo" class="img-fluid sm-logo"> -->
        @endif
        {{-- <img src="{{asset('public/img/logo.png')}}" alt="logo" class="img-fluid lg-logo">
        <img class="phone-logo" src="{{asset('public/img/sm-logo.png')}}" alt="logo" class="img-fluid sm-logo"> --}}
      </a>
      <div class="modal" id="loader_modal" role="dialog" style="position: absolute;overflow: hidden;left: auto;top: 3px;">
          <p style="text-align:center;"><img class="phone-modal-image" src="{{ asset('public/uploads/gif/waiting.gif') }}"></p>
      </div>
    </figure>
    </div>
    <div class="col topsearch-col p-0 m-1 d-flex align-items-center header__hide_phone">
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
      <div class="col topsearch-col p-0 m-1 d-flex align-items-center header__hide_phone">
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
      <div class="col topsearch-col p-0 m-1 d-flex align-items-center header__hide_phone">
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
      <div class="input-group-append ml-1 col p-0 pt-1 d-flex align-items-center header__hide_phone" style="border-radius: 2px;">

          <a class="btn recived-button custom-create-po-btn" target="_blank" href="{{ route('create-purchase-order-direct') }}" style="display:block;" id="create-quotation" title="Create Purchase Orders">
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



<!-- <div class="top-bar p-0">
  <div class="align-items-center ml-0 mr-0 row"> -->
<!-- <div class="col-xl-1 col-lg-1 col-md-2 col-sm-1 p-0 topsearches pr-5 headings-color top-notify">
{{Auth::user()->roles->name}}
</div> -->

  <!-- <div class="col-xl-9 col-lg-7 col-md-7 col-sm-7 topsearches pr-5">
    <div class="d-flex pl-3"> -->









    <!-- </div>
  </div> -->
  <!-- Header User Info Start Here -->
  <div class="col-xl-3 col-lg-4 col-md-8 col-sm-6 userlinks pr-4 d-flex justify-content-end">
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
        <div class="dropdown-menu bell-dropdown mt-2" style="box-shadow: 0px 0px 4px #a9a9a9; top: -5px !important; box-shadow: 0px 0px 4px #a9a9a9;width:300px; height: auto;max-height: 500px; @if($dummy_data != null && $dummy_data->count() >= 8) overflow-y:scroll; @endif">
          @if($dummy_data != null && $dummy_data->count() > 0)
          @foreach($dummy_data as $data)
            <div class="row m-1 @if($data->read_at == null) unread @endif bell-row" style="border: 1px solid black" id="grand-parent-div-{{$data->id}}">
              <div class="col-md-11 mt-2 mb-2 mr-2 bell-title">
                @php
                $column_data = json_decode($data->data);
                @endphp
                <h6>{{@$column_data->subject}}</h6>
                <span>{{strip_tags(@$column_data->body)}}</span>
              </div>
              @if(@$data->read_at == null)
              <div class="mt-2 mb-2 mr-0 bell-cross-btn" id="parent-div-{{$data->id}}">
                <a href="#" class="cross-btn" title="Clear" data-id = "{{$data->id}}"><img class="mobile-cross-btn" src="{{asset('public/img/cross.png')}}" width="15px" height="15px"></a>
              </div>
              @endif
            </div>
          @endforeach
          @if($dummy_data != null && @$dummy_data->where('read_at', null)->count() > 0)
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
    <li class="nav-item dropdown userdropdown ntifction-dropdown d-none">
 <a class="align-items-center d-flex nav-link dropdown-toggle  fa fa-bell notification" href="#" id="usernotification" data-toggle="dropdown" aria-expanded="true">
         <span class="badge">3</span>
        <!-- <img src="assets/img/notification.png" alt="notification image" class="img-fluid"> -->
      </a>
      <div class="dropdown-menu dropdown-menu-right notifications">
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
      </div>
    </li>
    <li class="nav-item dropdown prof-dropdown d-flex">
      <a class="align-items-center d-flex dropdown-toggle nav-link profilelink my_click" href="#" id="profilelink" data-toggle="dropdown">
      @if(Auth::user()->user_details != null)
      @if(Auth::user()->user_details->image != null && file_exists( public_path() . '/uploads/purchase/' . @Auth::user()->user_details->image))
        <img src="{{asset('public/uploads/purchase/'.@Auth::user()->user_details->image)}}" alt="user image" class="img-fluid rounded-circle img-fluid profileimg mobile-profileimg">
        <i class="fa fa-camera h-camera" style="
    display: none;
"></i>
        @else
        <img src="{{asset('public/img/default-profile-image.png')}}" alt="user image" class="img-fluid rounded-circle img-fluid profileimg mobile-profileimg">
        <i class="fa fa-camera h-camera" style="
    display: none;
"></i>

      @endif
      @else
      <img src="{{asset('public/img/default-profile-image.png')}}" alt="user image" class="img-fluid rounded-circle img-fluid profileimg mobile-profileimg">
      <i class="fa fa-camera h-camera" style="
    display: none;
"></i>

      @endif
        <span class="username text-center mobile-username"><b>{{@Auth::user()->roles->name}} :</b> {{Auth::user()->name}}</span>
      </a>
       <div style="display: none;">
        <form enctype="multipart/form-data" method="post" id="upload_form">

      <input type="file" name="profileimg" id="profile" onchange="validateImage()" style="">
       <button id="submitbutton" type="submit">Upload</button>
      </form>
      </div>
      <div class="dropdown-menu sale-drop" style="right: -15px !important;">
          {{--<a class="dropdown-item" href="{{url('profile-setting')}}">
            <span class="dropdown-icon oi oi-person"></span> Profile
          </a>--}}
          @if($current_version)
          <a class="dropdown-item" href="{{route('version')}}">
            <span class="dropdown-icon oi oi-person"></span> Version {{$current_version}}
          </a>
          @endif
          <a class="dropdown-item" href="{{url('change-password')}}">
          <span class="dropdown-icon oi oi-person"></span> Change Password
        </a>
            <!-- <a class="dropdown-item" href="#">
              <span class="dropdown-icon oi oi-account-logout"></span> Logout
            </a> -->
            <a class="dropdown-item check_log" href="{{ route('logout') }}" onclick="event.preventDefault();$('#if_session_expire').val('true');document.getElementById('logout-form').submit();">
              <span class="dropdown-icon oi oi-account-logout"></span> Logout
            </a>
            </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
        <input id="if_session_expire" name="if_session_expire" type="hidden" value="false">
        </form>
          {{--<div class="dropdown-divider"></div>
          <a class="dropdown-item" href="#">Help Center</a>
          <a class="dropdown-item" href="#">Ask Forum</a>
          <a class="dropdown-item" href="#">Keyboard Shortcuts</a>--}}
        </div>
    </li>
  </ul>
</div>
  <!-- Header User Info End Here -->
  </div>
</div>
</div>
</header>

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
@include('backend.layouts.session-expire')

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
</script>

