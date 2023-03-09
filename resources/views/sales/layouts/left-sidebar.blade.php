<aside class="sidebar color-white sidebarin" style="width: 65px;">
<div class="sidebarbg" style="width: 65px;"></div>
<nav class="navbar sidebarnav navbar-expand-sm">
<!-- Links -->
  <ul class="menu list-unstyled">
  <li class="nav-item">
  <a class="nav-link dashboardlink" href="javascript:void(0)">
        <i class="fa fa-bars dashboardIcon"></i> <span></span></a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="{{url('sales')}}" title="Dashboard">
        <img src="{{asset('public/site/assets/sales/img/dashboard-icon.png')}}" alt="" class="img-fluid">
         <span>Sales Dashboard </span></a>
    </li>



    <li class="nav-item">
      <a class="nav-link" href="{{route('product-order-invoice')}}" title="Create Quotation">
        <img src="{{asset('public/site/assets/sales/img/pencil-30.png')}}" alt="" class="img-fluid">
         <span>Create Quotation / Draft Invoice</span></a>
    </li>

    {{-- <li class="nav-item">
      <a class="nav-link" href="{{route('completed-quotations')}}" title="Order Dashboard">
        <img src="{{asset('public/site/assets/sales/img/quotation-icon.png')}}" alt="" class="img-fluid"> <span>Order Dashboard(Quotations)</span> <span class="linkcounter">{{ @$completeQuotationsCount }}</span>
      </a>
    </li> --}}

    <!-- supplier code from purchasing -->
    @if(Auth::user()->role_id == 3)
    <!-- <li class="nav-item">
      <a class="nav-link" href="{{ url('supplier') }}" title="Suppliers">
        <img src="{{asset('public/site/assets/purchasing/img/vendors-icon.png')}}" alt="" class="img-fluid"><span>Suppliers</span></a>
    </li> -->


    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" href="#" id="navbardrop2" data-toggle="dropdown" title="Product Management">
        <img src="{{asset('public/site/assets/sales/img/product-catalog-icon.png')}}" alt="" class="img-fluid"> <span>Product Management</span>
      </a>
      <div class="dropdown-menu drp-counter">
        <a class="dropdown-item" href="{{ route('complete-list-product') }}" title="Completed Products">Completed Products<span class="linkcounter sub-linkcounter">{{ @$completeProducts }}</span></a>
        <a class="dropdown-item" href="{{ route('inquiry-products') }}" title="Inquiry Products">Inquiry Products<span class="linkcounter sub-linkcounter">{{ @$inquiryProducts }}</span></a>
      </div>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="{{ route('list-customer') }}" title="Customers Center">
        <img src="{{asset('public/site/assets/sales/img/customer.png')}}"  alt="" width="10" class="img-fluid">
         <span>Customers Center</span></a>
    </li>

    <li class="nav-item d-none">
      <a class="nav-link" href="{{route('account-recievable')}}" title="Account Payable">
        <img src="{{asset('public/site/assets/sales/img/customer-icon.png')}}"  alt="" class="img-fluid">
         <span>Account Payable</span></a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="{{route('account-recievable')}}" title="Account Recievables">
        <img src="{{asset('public/site/assets/sales/img/customer-icon.png')}}"  alt="" class="img-fluid">
         <span>Account Recievables</span></a>
    </li>

    @endif

    @if(Auth::user()->role_id == 4)
    <li class="nav-item">
      <a class="nav-link" href="{{ route('complete-list-product') }}" title="Products List">
        <img src="{{asset('public/site/assets/purchasing/img/invoice-icon.png')}}" alt="" class="img-fluid"><span>Complete Products</span></a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="{{ route('list-customer') }}" title="Customers List">
        <img src="{{asset('public/site/assets/sales/img/customer.png')}}" alt="" class="img-fluid">
         <span>Customers List</span></a>
    </li>

    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" href="#" id="navbardrop2" data-toggle="dropdown" title="Product Standards">
        <img src="{{asset('public/site/assets/backend/img/reports-icon.png')}}" alt="" class="img-fluid"> <span>Reports</span>
      </a>
      <div class="dropdown-menu">
        <a class="dropdown-item" href="{{ route('stock-report') }}"><span>Stock Movement Report</span></a>
        <a class="dropdown-item" href="{{ route('sold-products-report') }}"> Sold Products Report</a>
      </div>
    </li>
    @endif
     @if(Auth::user()->role_id == 3)
     <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" href="#" id="navbardrop2" data-toggle="dropdown" title="Product Standards">
        <img src="{{asset('public/site/assets/backend/img/reports-icon.png')}}" alt="" class="img-fluid"> <span>Reports</span>
      </a>
      <div class="dropdown-menu">
       <!--  <a class="dropdown-item" href="{{ route('stock-report') }}"><span>Stock Movement Report</span></a> -->
        <!-- <a class="dropdown-item" href="{{ route('customer-sales-report') }}"> Customer Sales Report</a> -->
           <a class="dropdown-item" href="{{ route('stock-report') }}"><span>Stock Movement Report</span></a>
        <a class="dropdown-item" href="{{ route('sold-products-report') }}"> Sold Products Report</a>
      </div>
    </li>
    @endif

    <li class="nav-item">
      <a class="nav-link" href="{{route('get-cancelled-orders')}}" title="Cancelled Orders">
        <img src="{{asset('public/site/assets/sales/img/cross.png')}}" alt="" class="img-fluid">
         <span>Cancelled Orders <span class="linkcounter sub-linkcounter" style="top: 0px;">{{ @$cancelledOrders }}</span></span></a>
    </li>

  </ul>
</nav>
</aside>
