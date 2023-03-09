<aside class="sidebar color-white sidebarin" style="width: 70px;">
<div class="sidebarbg" style="width: 70px;"></div>  
<nav class="navbar sidebarnav navbar-expand-sm">


<!-- Links -->
  <ul class="menu list-unstyled">
    <li class="nav-item active">
      <a class="nav-link dashboardlink" href="javascript:void(0)">
        <i class="fa fa-bars dashboardIcon"></i></a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="{{url('/')}}" title="Purchasing Dashboard">
        <img src="{{asset('public/site/assets/purchasing/img/dashboard-icon.png')}}"  alt="" class="img-fluid">
         <span>Purchasing Dashboard</span></a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="{{url('transfer-document-dashboard')}}" title="{{$global_terminologies['transfer_document']}} Dashboard">
        <img src="{{asset('public/site/assets/purchasing/img/dashboard-icon.png')}}"  alt="" class="img-fluid">
         <span>{{$global_terminologies['transfer_document']}} Dashboard</span></a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="{{ route('purchasing-report') }}" title="Purchasing Report">
        <img src="{{asset('public/site/assets/purchasing/img/invoice-icon.png')}}" alt="" class="img-fluid"><span>Purchasing Report</span></a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="{{ route('list-purchasing') }}" title="Purchase List">
        <img src="{{asset('public/site/assets/purchasing/img/invoice-icon.png')}}" alt="" class="img-fluid"><span>Purchase List</span></a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="{{ route('create-purchase-order-direct') }}" title="Create Purchase Orders">
        <img src="{{asset('public/site/assets/purchasing/img/pencil-30.png')}}" alt="" class="img-fluid"><span>Create Purchase Order</span></a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="{{ route('create-transfer-document') }}" title="Create Transfer Document">
        <img src="{{asset('public/site/assets/purchasing/img/pencil-30.png')}}" alt="" class="img-fluid"><span>Create Transfer Document</span></a>
    </li>

    {{--<li class="nav-item">
      <a class="nav-link" href="{{ route('purchase-orders') }}" title="Purchase Order Dashboard">
        <img src="{{asset('public/site/assets/purchasing/img/quotation-icon.png')}}" alt="" class="img-fluid"><span>Purchase Order Dashboard</span></a>
    </li>--}}

    <li class="nav-item d-none">
      {{--<a class="nav-link" href="{{ route('list-draft-invoices') }}" title="Draft Invoices">--}}
      <a class="nav-link" href="{{ url('sales/draft_invoices') }}" title="Draft Invoices">
        <img src="{{asset('public/site/assets/purchasing/img/quotation-icon.png')}}" alt="" class="img-fluid"> <span>Draft Invoices</span> <span class="linkcounter">{{ @$purchasingdraftInvoiceCount }}</span>
      </a>
    </li>

    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" href="#" id="navbardrop2" data-toggle="dropdown" title="Suppliers Center">
        <img src="{{asset('public/site/assets/purchasing/img/vendors-icon.png')}}" alt="" class="img-fluid"> <span>Suppliers Center</span>
      </a>
      <div class="dropdown-menu drp-counter">
        <a class="dropdown-item" href="{{ route('list-of-suppliers') }}">Suppliers</a>
        <a class="dropdown-item" href="{{ route('bulk-upload-suppliers-form') }}"><span>Suppliers Bulk Upload</span></a>         
      </div>
    </li>

    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" href="#" id="navbardrop2" data-toggle="dropdown" title="Products Management">
        <img src="{{asset('public/site/assets/purchasing/img/product-catalog-icon.png')}}" alt="" class="img-fluid"> <span>Products Management</span>
      </a>
      <div class="dropdown-menu drp-counter">
       <!--  <a class="dropdown-item" href="{{ route('adding-product') }}">Add Products</a> -->
        <a class="dropdown-item" href="{{ route('complete-list-product') }}">Complete Products <span class="linkcounter sub-linkcounter">{{ @$completeProducts }}</span></a>
        <a class="dropdown-item" href="{{ route('deactivate-list-product') }}">Deactivated Products <span class="linkcounter sub-linkcounter"> {{ @$deactivatedProducts }} </span></a>
        <a class="dropdown-item" href="{{ route('incomplete-list-product') }}">Incomplete Products <span class="linkcounter sub-linkcounter">{{ @$incompleteProducts }}</span></a>
        <a class="dropdown-item" href="{{ route('inquiry-products-to-purchasing') }}">Inquiry Products<span class="linkcounter sub-linkcounter">{{ @$inquiryProducts }}</span></a>
        <!-- <a class="dropdown-item" href="{{ route('bulk-products-upload-form',1) }}"><span>Products Bulk Upload</span></a> 
        <a class="dropdown-item" href="{{ route('bulk-prices-upload-form') }}"><span>Prices Bulk Upload</span></a>   -->
        
        <a class="dropdown-item" href="{{ route('bulk-quantity-upload-form') }}"><span>Stock Adjustments</span></a>
      </div>
    </li>

    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" href="#" id="navbardrop2" data-toggle="dropdown" title="Reports">
        <img src="{{asset('public/site/assets/backend/img/memo-icon.png')}}" alt="" class="img-fluid"> <span>Reports</span>
      </a>
      <div class="dropdown-menu">
        <a class="dropdown-item" href="{{ route('stock-report') }}"><span>Stock Movement Reports</span></a>
        <a class="dropdown-item" href="{{ route('sold-products-report') }}"> Sold Products Report</a> 
      </div>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="{{route('purchase-account-payable')}}" title="Account Payable">
        <img src="{{asset('public/site/assets/sales/img/customer-icon.png')}}"  alt="" class="img-fluid">
         <span>Account Payables</span></a>
    </li>

    <!-- <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" href="#" id="navbardrop2" data-toggle="dropdown" title="General Settings">
        <img src="{{asset('public/site/assets/purchasing/img/configuration-icon.png')}}" alt="" class="img-fluid"> <span>Settings</span>
      </a>
      <div class="dropdown-menu drp-counter">
        <a class="dropdown-item" href="{{route('general-settings')}}">General Settings</a>
      </div>
    </li>     -->

  </ul>
</nav>
</aside>