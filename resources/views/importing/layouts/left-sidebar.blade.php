<aside class="sidebar color-white sidebarin" style="width: 65px;">
<div class="sidebarbg" style="width: 65px;"></div>  
<nav class="navbar sidebarnav navbar-expand-sm">
<!-- Links -->
  <ul class="menu list-unstyled">
  <li class="nav-item">
  <a class="nav-link dashboardlink" href="javascript:void(0)">
        <i class="fa fa-bars dashboardIcon"></i> <span></span></a>
    </li>
    <li class="nav-item d-none">
      <a class="nav-link" href="{{url('importing/')}}" title="Dashboard">
        <img src="{{asset('public/site/assets/purchasing/img/dashboard-icon.png')}}"  alt="" class="img-fluid">
         <span>Dashboard</span></a>
    </li>

    <li class="nav-item d-none">
      <a class="nav-link" href="{{ route('incompleted-po-groups') }}" title="Product Receiving Dashboard">
        <img src="{{asset('public/site/assets/purchasing/img/dashboard-icon.png')}}"  alt="" class="img-fluid">
         <span>Product Receiving Dashboard Old</span></a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="{{ route('importing-receiving-queue') }}" title="Product Receiving Dashboard">
        <img src="{{asset('public/site/assets/backend/img/purchasing.png')}}"  alt="" class="img-fluid">
         <span>Product Receiving Dashboard</span></a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="{{url('warehouse/')}}" title="Pick Instructions">
        <img src="{{asset('public/site/assets/purchasing/img/invoice-icon.png')}}"  alt="" class="img-fluid">
         <span>Pick Instructions</span></a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="{{ route('complete-list-product') }}" title="Completed Products">
        <img src="{{asset('public/site/assets/purchasing/img/product-catalog-icon.png')}}"  alt="" class="img-fluid">
         <span>Complete Products <span class="linkcounter sub-linkcounter" style="top:0;">{{ @$completeProducts }}</span></span></a>
    </li>
    <!-- <li class="nav-item">
      <a class="nav-link" href="{{ route('receiving-queue') }}" title="Issue Purchase List">
        <img src="{{asset('public/site/assets/purchasing/img/invoice-icon.png')}}" alt="" class="img-fluid"><span>Recieving Queue</span></a>
    </li> -->
    

  </ul>
</nav>
</aside>