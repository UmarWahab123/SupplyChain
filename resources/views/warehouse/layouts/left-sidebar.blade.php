<aside class="sidebar color-white sidebarin" style="width: 65px;">
<div class="sidebarbg" style="width: 65px;"></div>  
<nav class="navbar sidebarnav navbar-expand-sm">
<!-- Links -->
  <ul class="menu list-unstyled">
    <li class="nav-item">
      <a class="nav-link dashboardlink" href="javascript:void(0)">
        <i class="fa fa-bars dashboardIcon"></i> <span></span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="{{url('warehouse/')}}" title="Dashboard">
        <img src="{{asset('public/site/assets/purchasing/img/dashboard-icon.png')}}"  alt="" class="img-fluid">
         <span>Pick Instruction Dashboard</span></a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="{{url('warehouse/warehouse-transfer-document-dashboard')}}" title="Transfer Document Dashboard">
        <img src="{{asset('public/site/assets/purchasing/img/dashboard-icon.png')}}"  alt="" class="img-fluid">
         <span>Transfer Document Dashboard</span></a>
    </li>

    <li class="nav-item d-none">
      <a class="nav-link" href="{{ route('warehouse-incompleted-po-groups') }}" title="Receiving Queue">
        <img src="{{asset('public/site/assets/purchasing/img/invoice-icon.png')}}" alt="" class="img-fluid"><span>Receiving Queue Old</span></a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="{{ route('warehouse-receiving-queue') }}" title="Receiving Queue">
        <img src="{{asset('public/site/assets/backend/img/purchasing.png')}}" alt="" class="img-fluid"><span>Receiving Queue</span></a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="{{ route('warehouse-incompleted-transfer-groups') }}" title="Transfer Receiving Queue">
        <img src="{{asset('public/site/assets/backend/img/purchasing.png')}}"  alt="" class="img-fluid"><span>Transfer Receiving Queue</span></a>
    </li>

    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" href="#" id="navbardrop2" data-toggle="dropdown" title="Products Management">
        <img src="{{asset('public/site/assets/purchasing/img/product-catalog-icon.png')}}" alt="" class="img-fluid"> <span>Products Management</span>
      </a>
      <div class="dropdown-menu">
        <a class="dropdown-item" href="{{ route('complete-list-product') }}"><span>Products List</span></a>
        <a class="dropdown-item" href="{{ route('warehouse-stock-adjustment') }}"><span>Stock Adjustment</span></a>
      </div>
    </li>

    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" href="#" id="navbardrop2" data-toggle="dropdown" title="Reports">
        <img src="{{asset('public/site/assets/backend/img/memo-icon.png')}}" alt="" class="img-fluid"> <span>Reports</span>
      </a>
      <div class="dropdown-menu">
        <a class="dropdown-item" href="{{ route('stock-report') }}"><span>Stock Movement Reports</span></a>
      </div>
    </li>

   <!--  <li class="nav-item">
    <a class="nav-link" href="{{ route('complete-list-product') }}" title="Completed Products">Completed Products<span class="linkcounter sub-linkcounter">{{ @$completeProducts }}</span></a>
  </li> -->
    
   <!--  <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" href="#" id="navbardrop2" data-toggle="dropdown" title="Products Management">
        <img src="{{asset('public/site/assets/purchasing/img/product-catalog-icon.png')}}" alt="" class="img-fluid"> <span>Products Management</span>
      </a>
      <div class="dropdown-menu drp-counter">
      </div>
    </li> -->

  </ul>
</nav>
</aside>