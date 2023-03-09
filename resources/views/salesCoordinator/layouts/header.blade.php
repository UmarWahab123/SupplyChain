@php
use App\Models\Common\ProductCategory;
use Carbon\Carbon;
@endphp
<header class="header fixed-top bg-white">
<div class="d-flex toprow position-relative">
 <figure class="align-items-center justify-content-center d-flex logo">
   <a href="{{url('admin')}}">
    @if (($sys_logos->logo != null) && ($sys_logos->small_logo != null))
      <img src="{{asset('public/uploads/logo/'.$sys_logos->logo)}}" alt="logo" class="img-fluid lg-logo">
      <img src="{{asset('public/uploads/logo/'.$sys_logos->small_logo)}}" alt="logo" class="img-fluid sm-logo">
    @else
      <img src="{{asset('public/img/logo.png')}}" alt="logo" class="img-fluid lg-logo">
      <img src="{{asset('public/img/logo-icon.png')}}" alt="logo" class="img-fluid sm-logo">
    @endif
    {{-- <img src="{{asset('public/img/logo.png')}}" alt="logo" class="img-fluid lg-logo">
    <img src="{{asset('public/img/logo-icon.png')}}" alt="logo" class="img-fluid sm-logo"> --}}
  </a>
  <div class="modal" id="loader_modal" role="dialog" style="position: absolute;right: -100%;overflow: hidden;left: auto;top: -2px;">
      <p style="text-align:center;"><img src="{{ asset('public/uploads/gif/waiting.gif') }}"></p>
  </div>
</figure>
<div class="top-bar col-xl-12 p-0">
  <div class="align-items-center ml-0 mr-0 row">

<!-- <div class="col-xl-2 col-lg-1 col-md-1 col-sm-1 p-0 topsearches mt-2 headings-color top-notify">
  <p>Sales Coordinator</p>
</div> -->
<div class="col-xl-9 col-lg-8 col-md-8 col-sm-7 topsearches pr-0">
    <div class="justify-content row">

        <div class="col-md-3 col-sm-4 topsearch-col p-0">
         <div class="border rounded input-group custom-input-group">
          <div class="input-group-prepend">
          <span class="input-group-text fa fa-search"></span>
          </div>
          <input type="text" id="header_prod_search" name="prod_name" class="form-control pl-1" placeholder="Search Products">
        </div>
          <p id="myId"></p>
        </div>

        <div class="col-md-3 col-sm-4 topsearch-col p-0 ml-1">
        <div class="border rounded input-group custom-input-group">
          <div class="input-group-prepend">
          <span class="input-group-text fa fa-search"></span>
          </div>
          <input type="text" class="form-control pl-1" id="header_orders_search" name="header_orders_search" placeholder="Search Orders/Quotation">
        </div>
        <p id="myId2"></p>
    </div>

        <div class="input-group-append ml-3 col-md-2 p-0">
      <button class="btn recived-button p-0" type="submit">
      <a class="" href="{{route('product-order-invoice')}}" style="display:block;" id="create-quotation">
        <!-- <img src="{{asset('public/site/assets/sales/img/pencil-30.png')}}" alt="" class="img-fluid"> -->
         <span>Create Quotation</span></a>
      </button>
     </div>
    </div>
  </div>
  <!-- Header User Info Start Here -->
  <div class="col-xl-3 col-lg-4 col-md-4  col-sm-5 col-12 userlinks d-flex justify-content-end">

          <!-- Bell Notifications -->
<div class="d-flex align-items-center">
   <ul class="align-items-center justify-content-end d-flex list-unstyled mb-0">
      <li class="nav-item dropdown prof-dropdown d-flex">
        <a class="align-items-center d-flex dropdown-toggle nav-link my_click" href="#"data-toggle="dropdown">
          <img src="{{asset('public/img/bell.png')}}" style="height: 35px; width: 35px">
            @if($dummy_data != null && $dummy_data->where('read_at', null)->count() != 0)
            <span class="badge badge-custom">
              {{$dummy_data->where('read_at', null)->count()}}
            </span>
            @endif
        </a>
        <div class="dropdown-menu bell-dropdown mt-2" style="box-shadow: 0px 0px 4px #a9a9a9; top: -5px !important; max-height: 500px;width:300px; height: auto; @if($dummy_data != null && $dummy_data->count() >= 8) overflow-y:scroll; @endif">
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
                <a href="#" class="cross-btn" title="Clear" data-id = "{{$data->id}}"><img src="{{asset('public/img/cross.png')}}" width="10px" height="10px"></a>
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
    <li class="nav-item dropdown userdropdown ntifction-dropdown">
        <a class="align-items-center d-flex nav-link dropdown-toggle  fa fa-bell notification" href="#" id="usernotification" data-toggle="dropdown" aria-expanded="true">
         <span class="badge" id="badge">{{Auth()->user()->Notifications->count()}}</span>
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
             <div class="col-lg-3 p-0">
               <img src="{{asset('public/img/profileImg.jpg')}}" alt="user image" class="img-fluid rounded-circle img-fluid ml-2 mt-1" style="width: 60%;height: auto;">
             </div>
             <div class="col-lg-9 p-0">
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
    <li class="nav-item dropdown prof-dropdown">
      <a class="align-items-center d-flex dropdown-toggle nav-link profilelink" href="#" id="profilelink" data-toggle="dropdown">

        @if(@Auth::user()->user_details->image != null && file_exists( public_path() . '/uploads/sales/images/' . @Auth::user()->user_details->image))
        <img src="{{asset('public/uploads/sales/images/'.@Auth::user()->user_details->image)}}" alt="user image" class="img-fluid rounded-circle img-fluid profileimg">
        @else
        <img src="{{asset('public/img/profileImg.jpg')}}" alt="user image" class="img-fluid rounded-circle img-fluid profileimg">
        @endif
        <span class="username text-center"><b>{{@Auth::user()->roles->name}} :</b> {{Auth::user()->name}}</span>
      </a>
      <div class="dropdown-menu sale-drop" style="right: -15px !important;">
        {{--<a class="dropdown-item" href="{{url('sales/profile-setting')}}">
          <span class="dropdown-icon oi oi-person"></span> Profile
        </a>--}}
        @if($current_version)
        <a class="dropdown-item" href="{{route('version')}}">
          <span class="dropdown-icon oi oi-person"></span> Version {{$current_version}}
        </a>
        @endif
        <a class="dropdown-item" href="{{url('sales/change-password')}}">
          <span class="dropdown-icon oi oi-person"></span> Change Password
        </a>
        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();$('#if_session_expire').val('true');document.getElementById('logout-form').submit();">
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
  </div>
</div>
</div>
</header>
@include('backend.layouts.session-expire')
