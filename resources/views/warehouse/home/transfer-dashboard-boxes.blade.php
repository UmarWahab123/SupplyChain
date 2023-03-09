

<div class="col-lg col-md-4 pb-3 ">
<a class="my-pos" data-id='20' title="Waiting Confirmation" style="cursor: pointer; ">
<div class="bg-white box1 py-4 px-3">
  <div class="d-flex align-items-center justify-content-center">
    <img src="{{asset('public/img/client.jpg')}}" class="img-fluid">
    <div class="title pl-lg-3 pl-2">
      <h6 class="mb-0 number-size text-center">{{$waitingConfirmTd}}</h6>
      <span class="span-color">{{$page_status[0]}}</span>
    </div>
  </div>
</div>
</a>
</div>

<div class="col-lg col-md-4 pb-3 ">
<a class="my-pos" data-id='21' title="Waiting Transfer" style="cursor: pointer; ">
<div class="bg-white box2 py-4 px-3">
  <div class="d-flex align-items-center justify-content-center">
    <img src="{{asset('public/img/client.jpg')}}" class="img-fluid">
    <div class="title pl-lg-3 pl-2">
      <h6 class="mb-0 number-size text-center">{{$waitingTransfer}}</h6>
      <span class="span-color">{{$page_status[1]}}</span>
    </div>
  </div>
</div>
</a>
</div>

<div class="col-lg col-md-4 pb-3 ">
<a class="my-pos" data-id='22' title="Complete Transfer" style="cursor: pointer; ">
<div class="bg-white box3 py-4 px-3">
  <div class="d-flex align-items-center justify-content-center">
    <img src="{{asset('public/img/client.jpg')}}" class="img-fluid">
    <div class="title pl-lg-3 pl-2">
      <h6 class="mb-0 number-size text-center">{{$completetransfer}}</h6>
      <span class="span-color">{{$page_status[2]}}</span>
    </div>
  </div>
</div>
</a>
</div>