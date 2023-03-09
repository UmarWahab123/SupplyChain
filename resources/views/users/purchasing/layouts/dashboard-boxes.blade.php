<link rel="stylesheet" href="{{ asset('public/site/assets/backend/css/sales-dashboard.css') }}">

<div class="col-lg col-md-4 pb-3 ">
<a href="{{url('/')}}" class="my-pos" data-id='12' title="{{$page_status[0]}}" style="cursor: pointer; ">
<div class="bg-white box1 py-4 px-3 h-100 dashboard-boxes-shadow ">
  <div class="d-flex align-items-center justify-content-center dashboard-alignment">
    <img src="{{asset('public/img/purchase-waitnig-confirmation.png')}}" class="img-fluid">
    <div class="title pl-lg-3 pl-2">
      <h6 class="mb-0 number-size text-center">{{$waitingConfirmPo}}</h6>
      <span class="span-color">{{$page_status[0]}}</span>
    </div>
  </div>
</div>
</a>
</div>

<div class="col-lg col-md-4 pb-3 ">
<a href="{{url('/waiting-shipping-info')}}" class="my-pos" data-id='13' title="{{$page_status[1]}}" style="cursor: pointer; ">
<div class="bg-white box2 py-4 px-3 h-100 dashboard-boxes-shadow">
  <div class="d-flex align-items-center justify-content-center dashboard-alignment ">
    <img src="{{asset('public/img/purchase-shipping-info.png')}}" class="img-fluid">
    <div class="title pl-lg-3 pl-2">
      <h6 class="mb-0 number-size text-center">{{$shippedPo}}</h6>
      <span class="span-color">{{$page_status[1]}}</span>
    </div>
  </div>
</div>
</a>
</div>

<div class="col-lg col-md-4 pb-3 ">
<a href="{{url('/dispatch-from-supplier')}}" class="my-pos" data-id='14' title="{{$page_status[2]}}" style="cursor: pointer; ">
<div class="bg-white box3 py-4 px-3 h-100 dashboard-boxes-shadow">
  <div class="d-flex align-items-center justify-content-center dashboard-alignment ">
    <img src="{{asset('public/img/purchase-dispatch.png')}}" class="img-fluid">
    <div class="title pl-lg-3 pl-2">
      <h6 class="mb-0 number-size text-center">{{$dispatchedPo}}</h6>
      <span class="span-color">{{$page_status[2]}}</span>
    </div>
  </div>
</div>
</a>
</div>

<div class="col-lg col-md-4 pb-3 ">
<a href="{{url('/received-into-stock')}}" class="my-pos" data-id='15' title="{{$page_status[3]}}" style="cursor: pointer; ">
<div class="bg-white box4 py-4 px-3 h-100 dashboard-boxes-shadow">
  <div class="d-flex align-items-center justify-content-center dashboard-alignment ">
    <img src="{{asset('public/img/purchase-order-into-stock.png')}}" class="img-fluid">
    <div class="title pl-lg-3 pl-2">
      <h6 class="mb-0 number-size text-center">{{$receivedPoCurrentMonth}}</h6>
      <span class="span-color">{{$page_status[3]}}</span>
    </div>
  </div>
</div>
</a>
</div>

<div class="col-lg col-md-4 pb-3">
  <a  href="{{url('/all-pos')}}" title="All POs">
<div class="bg-white box5 py-4 px-3 h-100 dashboard-boxes-shadow">
  <div class="d-flex align-items-center justify-content-center dashboard-alignment ">
    <img src="{{asset('public/img/purchase-pos.png')}}" class="img-fluid">
    <div class="title pl-lg-3 pl-2">
      <h6 class="mb-0 number-size text-center">{{$allPo}}</h6>
      <span class="span-color">All POs</span>
    </div>
  </div>
</div>
  </a>
</div>

<div class="col-lg col-md-4 pb-3 ">
  <a  href="{{ route('inquiry-products-to-purchasing') }}" title="Inquiry Products">
<div class="bg-white box5 py-4 px-3 h-100 dashboard-boxes-shadow">
  <div class="d-flex align-items-center justify-content-center dashboard-alignment ">
    <img src="{{asset('public/img/purchase-inquairy.png')}}" class="img-fluid">
    <div class="title pl-lg-3 pl-2">
      <h6 class="mb-0 number-size text-center">{{$inquiryProducts}}</h6>
      <span class="span-color">Inquiry Products</span>
    </div>
  </div>
</div>
  </a>
</div>

{{--Its not in use now--}}
<div class="col-lg col-md-4 pb-3 d-none">
  <a  href="{{url('supplier')}}" title="Suppliers Center">
<div class="bg-white box5 py-4 px-3 h-100 dashboard-boxes-shadow">
  <div class="d-flex align-items-center justify-content-center dashboard-alignment ">
    <img src="{{asset('public/img/client.jpg')}}" class="img-fluid">
    <div class="title pl-lg-3 pl-2">
      <h6 class="mb-0 number-size text-center">{{$supplierCount}}</h6>
      <span class="span-color">Suppliers</span>
    </div>
  </div>
</div>
  </a>
</div>
