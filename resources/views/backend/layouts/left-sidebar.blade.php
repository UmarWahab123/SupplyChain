<aside class="sidebar color-white sidebarin" style="width: 70px;">
<div class="sidebarbg" style="width: 70px;"></div>
<nav class="navbar sidebarnav navbar-expand-sm">

<!-- Links -->
  <ul class="menu list-unstyled">
  <li class="nav-item acitve">
      <a class="nav-link dashboardlink" href="javascript:void(0)">
        <i class="fa fa-bars dashboardIcon mydashicon"></i>
        <!-- <img src="{{asset('public/site/assets/backend/img/dashboard.png')}}" alt="" class="img-fluid"> -->
        <span class="mydash"></span></a>
    </li>
    <li class="nav-item d-none">
      <a class="nav-link" href="{{url('admin')}}" title="Dashboard">
        <img src="{{asset('public/site/assets/backend/img/dashboard-icon.png')}}" alt="" class="img-fluid">
         <span>Admin Dashboard</span></a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="{{ url('/sales') }}" title="Sales Dashboard">
        <img src="{{asset('public/site/assets/backend/img/dashboard-icon.png')}}" alt="" class="img-fluid">
         <span>Sales Dashboard</span></a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="{{ url('/') }}" title="Purchasing Dashboard">
        <img src="{{asset('public/site/assets/backend/img/dashboard-icon.png')}}" alt="" class="img-fluid">
         <span>Purchasing Dashboard</span></a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="{{route('all-users-list')}}" title="Users">
        <img src="{{asset('public/site/assets/backend/img/overview-icon5.png')}}" alt="" class="img-fluid">
         <span>Users</span></a>
    </li>


    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" href="#" id="navbardrop2" data-toggle="dropdown" title="Customer Center">
        <img src="{{asset('public/site/assets/backend/img/customer-icon.png')}}" alt="" class="img-fluid"> <span>Customer Center</span>
      </a>
      <div class="dropdown-menu">

        <a class="dropdown-item" href="{{ route('list-customer') }}"> Customer List </a>
        <a class="dropdown-item" href="{{ route('get-cancelled-orders') }}"> Cancelled Orders <span class="linkcounter sub-linkcounter" style="top: 8px;">{{ @$cancelledOrders }}</span> </a>
        <a class="dropdown-item" href="{{ route('bulk-upload-customer-form') }}"> Customer Bulk Upload</a>
      </div>
    </li>

    <!-- <li class="nav-item">
      <a class="nav-link" href="{{ route('common_products') }}" title="Product List">
        <img src="{{asset('public/site/assets/backend/img/product-list.png')}}" alt="" class="img-fluid">
         <span>Product List</span></a>
    </li> -->


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
        {{-- <a class="dropdown-item" href="{{ route('bulk-products-upload-form',1) }}"><span>Products Bulk Upload</span></a>
        <a class="dropdown-item" href="{{ route('bulk-prices-upload-form') }}"><span>Prices Bulk Upload</span></a> --}}

        <a class="dropdown-item" href="{{ route('bulk-quantity-upload-form') }}"><span>Stock Adjustments</span></a>
      </div>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="{{ route('list-of-suppliers') }}" title="Supplier List">
        <img src="{{asset('public/site/assets/backend/img/supplier.png')}}" alt="" class="img-fluid">
         <span>Supplier List</span></a>
    </li>

    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" href="#" id="navbardrop2" data-toggle="dropdown" title="Product Standards">
        <img src="{{asset('public/site/assets/backend/img/memo-icon.png')}}" alt="" class="img-fluid"> <span>Product Standards</span>
      </a>
      <div class="dropdown-menu">
        <a class="dropdown-item" href="{{ route('product-categories-list') }}"> <img src="{{asset('public/site/assets/backend/img/product-list.png')}}" alt="" width='20' class="img-fluid">     Product Categories</a>
        <a class="dropdown-item" href="{{ route('product-type-list') }}"> <img src="{{asset('public/site/assets/backend/img/software-icon.png')}}" alt="" width='20' class="img-fluid">     Product Types</a>
        {{--<a class="dropdown-item" href="{{ route('brand-list') }}"> <img src="{{asset('public/site/assets/backend/img/overview-icon5.png')}}" alt="" width='20' class="img-fluid">    Brands</a>--}}
      </div>
    </li>

    <!-- <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" href="#" id="navbardrop2" data-toggle="dropdown" title="Users">
        <img src="{{asset('public/site/assets/backend/img/overview-icon5.png')}}" alt="" class="img-fluid"> <span>Users</span>
      </a>
      <div class="dropdown-menu">
        <a class="dropdown-item" href="{{ route('all-users-list') }}">
        <img src="{{asset('public/site/assets/backend/img/user-icon.png')}}" alt="" width='20' class="img-fluid">
        Users</a>
        <a class="dropdown-item" href="{{ route('purchasing-list') }}">
        <img src="{{asset('public/site/assets/backend/img/purchasing.png')}}" alt="" width='20' class="img-fluid">
        Purchasing</a>
        <a class="dropdown-item" href="{{ route('sales-list') }}"> <img src="{{asset('public/site/assets/backend/img/sales.png')}}" alt="" width='20' class="img-fluid"> Sales</a>
        <a class="dropdown-item" href="{{ route('sales-coordinators-list') }}"> <img src="{{asset('public/site/assets/backend/img/sales-cordinator.png')}}" alt="" width='20' class="img-fluid"> Sales Coordinators</a>
        <a class="dropdown-item" href="{{ route('importing-list') }}"> <img src="{{asset('public/site/assets/backend/img/importing.png')}}" alt="" width='20' class="img-fluid"> Importing</a>
        <a class="dropdown-item" href="{{ route('warehouse-list') }}"> <img src="{{asset('public/site/assets/backend/img/warehouse.png')}}" alt="" width='20' class="img-fluid"> Warehouse</a>
        <a class="dropdown-item" href="{{ route('accounting-list') }}"> <img src="{{asset('public/site/assets/backend/img/accounting.png')}}" alt="" width='20' class="img-fluid"> Accounting</a>
      </div>
    </li> -->

    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" href="#" id="navbardrop2" data-toggle="dropdown" title="Reports">
        <img src="{{asset('public/site/assets/backend/img/reports-icon.png')}}" alt="" class="img-fluid"> <span>Reports</span>
      </a>
      <div class="dropdown-menu">
        <a class="dropdown-item" href="{{ route('stock-report') }}"><span>Stock Movement Report</span></a>
        <a class="dropdown-item" href="{{ route('customer-sales-report') }}"> Customer Sales Report</a>
        <a class="dropdown-item" href="{{ route('purchasing-report') }}"> Purchasing Report</a>
        <a class="dropdown-item" href="{{ route('sold-products-report') }}"> Sold Products Report</a>
        <a class="dropdown-item" href="{{ route('product-sales-report') }}"> Product Sales Report</a>
        <a class="dropdown-item" href="{{ route('admin-sales-and-management-report') }}"> Admin Sales & Management Report</a>
      </div>
    </li>

    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" href="#" id="navbardrop2" data-toggle="dropdown" title="Global Configuration">
        <img src="{{asset('public/site/assets/backend/img/setting-icon.png')}}" alt="" class="img-fluid"> <span>Global Configuration</span>
      </a>
      <div class="dropdown-menu">
        <a class="dropdown-item" href="{{ route('roles-list') }}"> <img src="{{asset('public/site/assets/backend/img/Roles.png')}}" alt="" width='20' class="img-fluid"> Roles</a>
        <a class="dropdown-item" href="{{ route('show-company') }}"> <img src="{{asset('public/site/assets/backend/img/purchasing.png')}}" alt="" width='20' class="img-fluid"> Companies</a>
        <a class="dropdown-item" href="{{ route('show-doc-number-setting') }}"> <img src="{{asset('public/site/assets/backend/img/purchasing.png')}}" alt="" width='20' class="img-fluid"> Document <br>Number Settings</a>
        <a class="dropdown-item" href="{{ route('show-warehouses') }}"> <img src="{{asset('public/site/assets/backend/img/purchasing.png')}}" alt="" width='20' class="img-fluid"> Warehouses</a>
        <a class="dropdown-item" href="{{ route('customer-categories-list') }}"> <img src="{{asset('public/site/assets/backend/img/cutomer-category.png')}}" alt="" width='20' class="img-fluid"> Customer Categories</a>
        <a class="dropdown-item" href="{{ route('show-couriers') }}"> <img src="{{asset('public/site/assets/backend/img/cutomer-category.png')}}" alt="" width='20' class="img-fluid"> Couriers</a>
        <!-- <a class="dropdown-item" href="{{ route('status-list') }}">Statuses </a> -->

        <a class="dropdown-item" href="{{ route('unit-list') }}"> <img src="{{asset('public/site/assets/backend/img/unit.png')}}" alt="" width='20' class="img-fluid"> Units</a>

        <a class="dropdown-item" href="{{ route('country-list') }}"> <img src="{{asset('public/site/assets/backend/img/unit.png')}}" alt="" width='20' class="img-fluid"> Countries</a>

        <a class="dropdown-item" href="{{ route('district-list') }}"> <img src="{{asset('public/site/assets/backend/img/unit.png')}}" alt="" width='20' class="img-fluid"> Cities</a>

        <a class="dropdown-item" href="{{ route('payment-type-list') }}"> <img src="{{asset('public/site/assets/backend/img/payment-type.png')}}" alt="" width='20' class="img-fluid"> Payment Methods</a>
        <a class="dropdown-item" href="{{ route('payment-term-list') }}"> <img src="{{asset('public/site/assets/backend/img/payment-terms.png')}}" alt="" width='20' class="img-fluid"> Payment Terms</a>
        <a class="dropdown-item" href="{{ route('list-template') }}"> <img src="{{asset('public/site/assets/backend/img/email-template.png')}}" alt="" width='20' class="img-fluid"> Email Templates</a>
        <a class="dropdown-item" href="{{ route('currency-list') }}"> <img src="{{asset('public/site/assets/backend/img/currency-type.png')}}" alt="" width='20' class="img-fluid"> Currencies</a>
        {{--<a class="dropdown-item" href="{{ route('invoice-setting') }}"> <img src="{{asset('public/site/assets/backend/img/invoice-icon.png')}}" alt="" width='20' class="img-fluid"> Invoice Setting</a>
        <a class="dropdown-item" href="{{ route('purchase-order-setting') }}"> <img src="{{asset('public/site/assets/backend/img/purchasing.png')}}" alt="" width='20' class="img-fluid"> PO Setting</a>--}}
        <a class="dropdown-item" href="{{ route('list-configuration') }}"> <img src="{{asset('public/site/assets/backend/img/configuration.png')}}" alt="" width='20' class="img-fluid"> Configurations</a>
      </div>
    </li>
  </ul>
</nav>
</aside>

