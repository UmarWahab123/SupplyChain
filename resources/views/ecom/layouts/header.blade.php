@php
use App\Models\Common\ProductCategory;
use Carbon\Carbon;
@endphp
<header class="header fixed-top bg-white">
<div class="d-flex toprow position-relative">
  <div class="pt-2 col-xl-3 col-lg-3 col-md-4 col-sm-6 pl-0 d-flex">
        <!-- <a href="{{url('/sales')}}">
            <img src="{{asset('public/images/static-logo.png')}}" alt="logo" class="img-fluid pt-1">
          </a> -->
         <figure class="logo ml-2 w-100">
             <a href="{{url('/sales/')}}">
              @if (($sys_logos->logo != null) && ($sys_logos->small_logo != null))
                <img class="phone-logo" src="{{asset('public/uploads/logo/'.$sys_logos->logo)}}" alt="logo" style="max-height: 38px; width: auto">
                <!-- <img src="{{asset('public/uploads/logo/'.$sys_logos->small_logo)}}" alt="logo" class="img-fluid sm-logo"> -->
              @else
                <img class="phone-logo" src="{{asset('public/img/logo.png')}}" alt="logo" style="max-height: 38px; width: auto">
                <!-- <img src="{{asset('public/img/logo-icon.png')}}" alt="logo" class="img-fluid sm-logo"> -->
              @endif
              {{-- <img src="{{asset('public/img/logo.png')}}" alt="logo" class="img-fluid lg-logo">
              <img class="phone-logo" src="{{asset('public/img/logo-icon.png')}}" alt="logo" class="img-fluid sm-logo"> --}}
            </a>
            <div class="modal" id="loader_modal" role="dialog" style="width: 50%;position: absolute;overflow: hidden;left: 50%;top: 10px;">
                    <p style="text-align:center;"><img class="phone-modal-image" src="{{ asset('public/uploads/gif/waiting.gif') }}"></p>
            </div>
          </figure>
      </div>
      <div class="col topsearch-col p-0 m-1 d-flex align-items-center header__hide_phone">
         <div class="border rounded input-group custom-input-group">
          <div class="input-group-prepend">
          <span class="input-group-text fa fa-search"></span>
          </div>
          <input type="text" id="header_prod_search1" name="prod_name" class="form-control pl-1" placeholder="Search Products">
          <span id="sales_loader_product"></span>
        </div>
          <p id="myId"></p>
        </div>

        <div class="col topsearch-col p-0 m-1 d-flex align-items-center ml-1 header__hide_phone">
        <div class="border rounded input-group custom-input-group">
          <div class="input-group-prepend">
          <span class="input-group-text fa fa-search"></span>
          </div>
          <input type="text" class="form-control pl-1" id="header_orders_search1" name="header_orders_search" placeholder="Search Orders/Quotation">
          <span id="sales_loader_product2"></span>
        </div>
        <p id="myId2"></p>
    </div>
<!-- <div class="top-bar col-xl-12 col-lg-12 col-md-12 p-0">
  <div class="align-items-center ml-0 mr-0 row"> -->
    <!-- <div class="col-xl-1 col-lg-1 col-md-2 col-sm-2 p-0 topsearches pr-5 headings-color top-notify">
     {{Auth::user()->roles->name}}
    </div> -->
<!-- @if(Auth::user()->role_id == 9)
<div class="col-xl-9 col-lg-10 col-md-10 col-sm-7 topsearches pr-0">
  @else
<div class="col-xl-9 col-lg-10 col-md-10 col-sm-7 topsearches pr-0">
  @endif
    <div class="justify-content row">




        {{--<div class="input-group-append ml-3 ml-md-2 col-xl-2 col-lg-3 col-md-3 p-0">
     <!--  <button class="btn recived-button p-0" type="submit"> -->
      <a class="btn recived-button custom-recived-button" target="_blank" href="{{route('product-order-invoice')}}" style="display:block;" id="create-quotation" data-toggle="tooltip" data-placement="auto" data-original-title="Create Quotation">
        <!-- <img src="{{asset('public/site/assets/sales/img/pencil-30.png')}}" alt="" class="img-fluid"> -->
         <span>Create Quotation</span></a>
      <!-- </button>   -->
     </div>--}}
    </div>
  </div> -->
  <!-- Header User Info Start Here -->
  <div class="col-xl-3 col-lg-1 col-md-4 col-sm-6 userlinks d-flex justify-content-end">

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
         <span class="badge" id="badge">{{Auth()->user()->unreadNotifications->count()}}</span>
        <!-- <img src="assets/img/notification.png" alt="notification image" class="img-fluid"> -->
      </a>
      <div class="dropdown-menu dropdown-menu-right notifications" id="notifications">
        @foreach(Auth()->user()->Notifications as $notification)
        @php $user = App\User::select('name')->where('id',@$notification->data['user_id'])->first();
        $not_color = 'darkblue';
$from = $notification->created_at->format('y-m-d');
        $fromDate = Carbon::parse($from);
        $to = Carbon::now();
        $diff_in_days = $to->diffInDays($fromDate);
        $today_time = $to->addHours(5)->format('h:i:s');

        $time = $notification->created_at->format('H:i:s');
        $new = new DateTime($time);
        $old = new DateTime($to);

       $interval = $old->diff($new);


        $weeks = ' days ago';
        $diff_in_dayss = $diff_in_days;
        if($diff_in_days < 1){

        if($interval->h > 0){
        $weeks = ' hours ago';
        $diff_in_dayss = $interval->h;
      }else if($interval->m > 0){
      $weeks = ' mins ago';
        $diff_in_dayss = $interval->m;
    }else if($interval->s > 0){
    $weeks = ' sec ago';
        $diff_in_dayss = $interval->s;
  }
      }
          if($diff_in_days > 6){
          $weeks = ' weeks ago';
          $diff_in_dayss = floor($diff_in_days / 7);
          if($diff_in_dayss > 4){
          $weeks = ' months ago';
          $diff_in_dayss = floor($diff_in_dayss / 4.345);
          if($diff_in_dayss >= 11){
          $weeks = ' years ago';
          $diff_in_dayss = floor($diff_in_dayss / 11);
        }
        }

        }
        @endphp
        @if($notification->read_at != null)
        @php $not_color = 'lightblue'; @endphp
        @endif
        <div class="usercol notinfo {{$not_color}}" id="not{{$loop->index}}" style="position: relative;">
          <a href="{{url('sales/mark-read/'.$loop->index.'/'.$notification->data['product_id'])}}" class="notiflink">
           <div class="row m-0">
             <div class="col-lg-3 col-md-3 p-0">
               <img src="{{asset('public/img/profileImg.jpg')}}" alt="user image" class="img-fluid rounded-circle img-fluid ml-2 mt-1" style="width: 60%;height: auto;">
             </div>
             <div class="col-lg-9 col-md-9 p-0">
                <!-- <div class="notifi-name fontbold">{{@$notification->data['reference_code']}}
            </div>
            <div class="notifi-desc">{{@$notification->data['product']}}</div>
            <span class="notifi-date fontmed">By : {{@$user->name}}</span> -->
            <div class="notifi-name fontbold">{{@$notification->data['product']}}
            </div>
            <!-- <div class="notifi-desc">{{@$notification->data['product']}}</div> -->
            <span class="notifi-date fontmed">By : {{@$user->name}} <br> {{@$diff_in_dayss}}{{$weeks}}</span>
             </div>
           </div>

            <!-- <span class="notifi-date fontmed">2 min ago</span> -->
          </a>
          @if(@$notification->read_at == null)
          <a href="" data-id="{{$loop->index}}" class="mark{{$loop->index}} envelope-a" style="position: absolute;top: 20px;right: 10px;" id="marks-as-read">
          <span class="notification-read"><i class="fa fa-envelope envelope{{$loop->index}}" title="Mask as read" aria-hidden="true"></i></span></a>
          @else
           <a href="" data-id="{{$loop->index}}" class="mark{{$loop->index}} envelope-a" style="position: absolute;top: 20px;right: 10px;" id="marks-as-read">
          <span class="notification-read"><i class="fa fa-envelope-open envelope{{$loop->index}}" title="Mask as unread" aria-hidden="true"></i></span></a>
          @endif
         </div>
         @if($loop->index == 2)
         @break;
         @endif
         @endforeach
         <!-- <div class="usercol notinfo">
           <a href="#" class="notiflink">
            <div class="notifi-name fontbold">Jessica Caruso</div>
            <div class="notifi-desc">accepted your invitation to join the team.</div>
            <span class="notifi-date fontmed">2 min ago</span>
          </a>
         </div> -->
         <div class="text-center"><a href="{{url('sales/list-notifications')}}" style="display: block;" class="p-1">See All</a></div>
      </div>
    </li>
    <li class="nav-item dropdown prof-dropdown d-flex">
      <a class="align-items-center d-flex dropdown-toggle nav-link profilelink my_click" href="#" id="profilelink" data-toggle="dropdown">

        @if(@Auth::user()->user_details->image != null && file_exists( public_path() . '/uploads/sales/images/' . @Auth::user()->user_details->image))
        <img src="{{asset('public/uploads/sales/images/'.@Auth::user()->user_details->image)}}" alt="user image" class="img-fluid rounded-circle img-fluid profileimg mobile-profileimg">
                <i class="fa fa-camera h-camera" style="
    display: none;
"></i>

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

      <input type="file" name="profileimg" onchange="validateImage()" id="profile" style="">
       <button id="submitbutton" type="submit">Upload</button>
      </form>
      </div>
      <div class="dropdown-menu sale-drop" style="right: -15px !important;">
        {{--<a class="dropdown-item" href="{{url('sales/profile-setting')}}">
          <span class="dropdown-icon oi oi-person"></span> Profile
        </a>--}}
        <a class="dropdown-item" href="{{url('sales/change-password')}}">
          <span class="dropdown-icon oi oi-person"></span> Change Password
        </a>
        <a class="dropdown-item logout_status" href="{{ route('logout') }}" onclick="event.preventDefault();$('#if_session_expire').val('true');document.getElementById('logout-form').submit();">
            <span class="dropdown-icon oi oi-account-logout"></span> Logout
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
        <input id="if_session_expire" name="if_session_expire" type="hidden" value="false">
        </form>
          <!-- <div class="dropdown-divider"></div> -->
         <!--  <a class="dropdown-item" href="#">Help Center</a>
          <a class="dropdown-item" href="#">Ask Forum</a>
          <a class="dropdown-item" href="#">Keyboard Shortcuts</a> -->
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

<script type="text/javascript">
  function validateImage() {
  console.log("validateImage called");
  var formData = new FormData();

  var file = document.getElementById("profile").files[0];

  formData.append("Filedata", file);
  var t = file.type.split('/').pop().toLowerCase();
   if (t != "jpeg" && t != "jpg" && t != "png") {
    toastr.error('Uploaded file image must be jpeg, jpg or png.','Error!' , {
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
