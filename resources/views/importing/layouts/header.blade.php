<header class="header fixed-top bg-white">
<div class="d-flex toprow position-relative">
  <div class="pt-2 col-xl-3 col-lg-3 col-md-4 col-sm-6 pl-0">
    <div class="col d-flex justify-content-center">
      <!-- <a href="{{url('importing/importing-receiving-queue')}}">
            <img src="{{asset('public/images/static-logo.png')}}" alt="logo" class="img-fluid pt-1">
          </a> -->
           <figure class="logo ml-2 w-100 position-relative">
             <a href="{{url('importing/importing-receiving-queue')}}">
              @if (($sys_logos->logo != null) && ($sys_logos->small_logo != null))
                <img class="phone-logo" src="{{asset('public/uploads/logo/'.$sys_logos->logo)}}" alt="logo" class="" style="max-height: 38px; width: auto">
                <!-- <img src="{{asset('public/uploads/logo/'.$sys_logos->small_logo)}}" alt="logo" class="img-fluid sm-logo"> -->
              @else
                <img class="phone-logo" src="{{asset('public/img/logo.png')}}" alt="logo" class="" style="max-height: 38px; width: auto">
                <!-- <img src="{{asset('public/img/logo-icon.png')}}" alt="logo" class="img-fluid sm-logo"> -->
              @endif
              {{-- <img src="{{asset('public/img/logo.png')}}" alt="logo" class="img-fluid lg-logo">
              <img src="{{asset('public/img/logo-icon.png')}}" alt="logo" class="img-fluid sm-logo"> --}}
            </a>
            <div class="modal" id="loader_modal" role="dialog" style="width: 50%;position: absolute;overflow: hidden;left: 50%;top: 3px;">
                    <p style="text-align:center;"><img class="phone-modal-image" src="{{ asset('public/uploads/gif/waiting.gif') }}"></p>
            </div>
          </figure>
    </div>
  </div>

<!-- <div class="top-bar col-xl-12 col-lg-12 col-md-12 p-0">
  <div class="align-items-center ml-0 mr-0 row"> -->

<!-- <div class="col-xl-2 col-lg-2 col-md-2 p-0 col-sm-2 topsearches pr-5 headings-color top-notify"> -->
  <!-- <div class="col-xl-1 col-lg-1 col-md-2 col-sm-2 p-0 topsearches pr-5 headings-color top-notify">
   {{Auth::user()->roles->name}}
  </div> -->

<!-- </div> -->
<!-- <div class="col-xl-7 col-lg-8 col-md-8 col-sm-6 topsearches pr-0">
    <div class="justify-content row d-none">

        <div class="col-md-3 col-sm-4 topsearch-col p-0">
         <div class="border rounded input-group custom-input-group">
          <div class="input-group-prepend">
          <span class="input-group-text fa fa-search"></span>
          </div>
          <input type="text" id="header_prod_search" name="prod_name" class="form-control pl-1" placeholder="Search products">
        </div>
          <p id="myId"></p>

        </div>
        <div class="col-md-3 col-sm-4 topsearch-col p-0 ml-1">
        <div class="border rounded input-group custom-input-group">
          <div class="input-group-prepend">
          <span class="input-group-text fa fa-search"></span>
          </div>
          <input type="text" class="form-control pl-1" id="header_orders_search" name="header_orders_search" placeholder="Search Orders/quotation">
        </div>
        <p id="myId2"></p>
    </div>

    </div>
  </div> -->
  <!-- Header User Info Start Here -->
  <div class="col-xl-9 col-lg-2 col-md-8 col-sm-6 userlinks d-flex justify-content-end">

          <!-- Bell Notifications -->
<div class="d-flex align-items-center">
  @if(auth()->user()->parent_id != null)
          <div class="header__hide_phone">
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
        <div class="dropdown-menu bell-dropdown mt-2" style="box-shadow: 0px 0px 4px #a9a9a9; top: -5px !important;max-height: 500px; width:300px; height: auto; @if($dummy_data != null && $dummy_data->count() >= 8) overflow-y:scroll; @endif">
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
         <figure class="f-camera mb-0">
      @if(Auth::user()->user_details)
      @if(Auth::user()->user_details->image != null && file_exists( public_path() . '/uploads/importing/images/' . @Auth::user()->user_details->image))
        <img src="{{asset('public/uploads/importing/images/'.@Auth::user()->user_details->image)}}" alt="user image" class="img-fluid rounded-circle img-fluid profileimg mobile-profileimg">
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
</figure>

         <span class="username text-center mobile-username"><b>{{@Auth::user()->roles->name}} :</b> {{Auth::user()->name}}</span>
      </a>

      <div style="display: none;">
        <form enctype="multipart/form-data" method="post" id="upload_form">

      <input type="file" name="profileimg" id="profile" style="">
       <button id="submitbutton" type="submit">Upload</button>
      </form>
      </div>
      <div class="dropdown-menu sale-drop">
        {{--<a class="dropdown-item" href="{{route('importing-profile-setting')}}">
          <span class="dropdown-icon oi oi-person"></span> Profile
        </a>--}}
        @if($current_version)
        <a class="dropdown-item" href="{{route('version')}}">
          <span class="dropdown-icon oi oi-person"></span> Version {{$current_version}}
        </a>
        @endif
        <a class="dropdown-item" href="{{route('change-password-importing')}}">
          <span class="dropdown-icon oi oi-person"></span> Change Password
        </a>
        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();$('#if_session_expire').val('true');document.getElementById('logout-form').submit();">
            <span class="dropdown-icon oi oi-account-logout"></span> Logout
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
        <input id="if_session_expire" name="if_session_expire" type="hidden" value="false">
        </form>

      </div>
    </li>
  </ul>
</div>
  <!-- Header User Info End Here -->
  <!-- </div>
</div> -->
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
