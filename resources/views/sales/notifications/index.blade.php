@extends('sales.layouts.layout')

@section('title','Suppliers Management | Supply Chain')

@section('content')
@php
use App\Models\Common\ProductCategory;
use Carbon\Carbon
@endphp
<style type="text/css">
.invalid-feedback {
     font-size: 100%; 
}
.disabled:disabled{
  opacity:0.5;
  cursor: not-allowed; 
}
</style>

{{-- Content Start from here --}}
<div class="row mb-0">
  
  <div class="col-md-10 title-col">
    <h3 class="maintitle text-uppercase fontbold mb-0 mt-1">Notifications</h3>
  </div>
</div>


<div class="row entriestable-row mt-3">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">

       @foreach(Auth()->user()->Notifications as $notification)
        @php $user = App\User::where('id',@$notification->data['user_id'])->first(); 
        $not_color = 'lightgray';
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
        @php $not_color = 'gray'; @endphp
        @endif
        <div class="usercol notinfo {{$not_color}}" id="nott{{$loop->index}}" style="position: relative;border-top:1px solid #eee;">
          <a href="{{url('sales/mark-read/'.$loop->index.'/'.$notification->data['product_id'])}}" class="notiflink pt-0 pb-1">
           <div class="row m-0">
             <div class="col-lg-1 col-md-1 p-0">
               <!-- <img src="{{asset('public/img/profileImg.jpg')}}" alt="user image" class="img-fluid rounded-circle img-fluid ml-2 mt-1" style="width: 35%;height: auto;"> -->
                @if(@$user->user_details->image != null && file_exists( public_path() . '/uploads/purchase/' . @$user->user_details->image))
        <img src="{{asset('public/uploads/purchase/'.@$user->user_details->image)}}" alt="user image" class="img-fluid rounded-circle ml-2 mt-1" style="width: 35%;height: auto;">
        @else
        <img src="{{asset('public/img/profileImg.jpg')}}" alt="user image" class="img-fluid rounded-circle ml-2 mt-1" style="width: 35%;height: auto;">
        @endif
             </div>  
             <div class="col-lg-11 col-md-11 p-0">
                <div class="notifi-name fontbold">{{@$notification->data['product']}}
            </div> 
            <!-- <div class="notifi-desc">{{@$notification->data['product']}}</div> -->
            <span class="notifi-date fontmed">By : {{@$user->name}} {{@$diff_in_dayss}}{{$weeks}}</span>
             </div>   
           </div>
           
            <!-- <span class="notifi-date fontmed">2 min ago</span> -->
          </a>  
          @if(@$notification->read_at == null)
          <a href="" data-id="{{$loop->index}}" class="mark{{$loop->index}} envelope-a" style="position: absolute;top: 5px;right: 10px;" id="marks-as-read">
          <span class="notification-read"><i class="fa fa-envelope envelope{{$loop->index}} hasit" title="Mask as read" aria-hidden="true"></i></span></a>
          @else
           <a href="" data-id="{{$loop->index}}" class="mark{{$loop->index}} envelope-a" style="position: absolute;top: 5px;right: 10px;" id="marks-as-read">
          <span class="notification-read"><i class="fa fa-envelope-open envelope{{$loop->index}} hasit" title="Mask as unread" aria-hidden="true"></i></span></a>
          @endif
         </div>
         @endforeach
  

    </div>
    
  </div>
</div>


</div>
<!--  Content End Here -->



@endsection

@section('javascript')
<script type="text/javascript">
  $(function(e){

   });
</script>
@stop

