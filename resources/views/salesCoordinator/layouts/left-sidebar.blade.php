<aside class="sidebar color-white sidebarin" style="width: 65px;">
<div class="sidebarbg" style="width: 65px;"></div>  
<nav class="navbar sidebarnav navbar-expand-sm">
<!-- Links -->
  <ul class="menu list-unstyled">
  <li class="nav-item active">  
  <a class="nav-link dashboardlink" href="javascript:void(0)">
        <i class="fa fa-bars dashboardIcon"></i> <span></span></a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="{{url('sales-coordinator/dashboard')}}" title="Dashboard">
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
   <li class="nav-item">
      <a class="nav-link" href="{{ route('common_supplier') }}" title="Supplier List">
        <img src="{{asset('public/site/assets/backend/img/overview-icon5.png')}}" alt="" class="img-fluid">
         <span>Supplier List</span></a>
    </li>


    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" href="#" id="navbardrop2" data-toggle="dropdown" title="Product Management">
        <img src="{{asset('public/site/assets/sales/img/product-catalog-icon.png')}}" alt="" class="img-fluid"> <span>Product Management</span>
      </a>
      <div class="dropdown-menu drp-counter">
        <a class="dropdown-item" href="{{ route('products-list') }}" title="Completed Products">Completed Products<span class="linkcounter sub-linkcounter">{{ @$completeProducts }}</span></a>
        <a class="dropdown-item" href="{{ route('inquiry-products') }}" title="Inquiry Products">Inquiry Products<span class="linkcounter sub-linkcounter">{{ @$inquiryProducts }}</span></a>      
      </div>
    </li> 
    
  <!-- <li class="nav-item">
      <a class="nav-link" href="{{route('list-customer-sales-coordinator')}}" title="Customers Center">
        <img src="{{asset('public/site/assets/sales/img/customer.png')}}"  alt="" width="10" class="img-fluid">
         <span>Customers Center</span></a>
    </li> -->
     <li class="nav-item">
      <a class="nav-link" href="{{ route('list-customer') }}" title="Customers List">
        <img src="{{asset('public/site/assets/sales/img/customer.png')}}" alt="" class="img-fluid">
         <span>Customers List</span></a>
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

  </ul>
</nav>
</aside>