<aside class="sidebar color-white sidebarin" style="width: 65px;">
<div class="sidebarbg" style="width: 65px; background:{{$sys_color->system_color}}"></div>  
<nav class="navbar sidebarnav navbar-expand-sm">
<!-- Links -->
  <ul class="menu list-unstyled">
    <li class="nav-item">
      <a class="nav-link dashboardlink" href="javascript:void(0)">
        <i class="fa fa-bars dashboardIcon"></i> <span></span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="{{route('accounting-dashboard')}}" title="Dashboard">
        <img src="{{asset('public/site/assets/purchasing/img/dashboard-icon.png')}}"  alt="" class="img-fluid">
         <span>Accounting Dashboard</span></a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="{{ url('/sales') }}" title="Sales Dashboard">
        <img src="{{asset('public/site/assets/backend/img/dashboard-icon.png')}}" alt="" class="img-fluid">
         <span>Sales Dashboard</span></a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="{{ route('purchasing-dashboard') }}" title="Purchasing Dashboard">
        <img src="{{asset('public/site/assets/backend/img/dashboard-icon.png')}}" alt="" class="img-fluid">
         <span>Purchasing Dashboard</span></a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="{{route('create-credit-note')}}" title="Account Recievables">
        <img src="{{asset('public/site/assets/sales/img/customer-icon.png')}}"  alt="" class="img-fluid">
         <span>Create Credit Note</span></a>
    </li>

     <li class="nav-item">
      <a class="nav-link" href="{{route('create-debit-note')}}" title="Account Recievables">
        <img src="{{asset('public/site/assets/sales/img/customer-icon.png')}}"  alt="" class="img-fluid">
         <span>Create Debit Note</span></a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="{{route('purchase-account-payable')}}" title="Account Payable">
        <img src="{{asset('public/site/assets/sales/img/customer-icon.png')}}"  alt="" class="img-fluid">
         <span>Account Payables</span></a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="{{route('account-recievable')}}" title="Account Recievables">
        <img src="{{asset('public/site/assets/sales/img/customer-icon.png')}}"  alt="" class="img-fluid">
         <span>Account Recievables</span></a>
    </li>

     

   <!--  <li class="nav-item">
      <a class="nav-link" href="{{ route('list-customer') }}" title="Customers List">
        <img src="{{asset('public/site/assets/sales/img/customer.png')}}" alt="" class="img-fluid">
         <span>Customers List</span></a>
    </li> -->

    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" href="#" id="navbardrop2" data-toggle="dropdown" title="Customer Center">
        <img src="{{asset('public/site/assets/backend/img/customer.png')}}" alt="" class="img-fluid"> <span>Customer Center</span>
      </a>
      <div class="dropdown-menu">
        
        <a class="dropdown-item" href="{{ route('list-customer') }}"> Customer List </a>
        <a class="dropdown-item" href="{{ route('get-cancelled-orders') }}"> Cancelled Orders <span class="linkcounter sub-linkcounter" style="top: 8px;">{{ @$cancelledOrders }}</span> </a>
        <!-- <a class="dropdown-item" href="{{ route('bulk-upload-customer-form') }}"> Customer Bulk Upload</a> -->
      </div>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="{{ route('list-of-suppliers') }}" title="Supplier List">
        <img src="{{asset('public/site/assets/backend/img/supplier.png')}}" alt="" class="img-fluid">
         <span>Supplier List</span></a>
    </li>

    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" href="#" id="navbardrop2" data-toggle="dropdown" title="Product Standards">
        <img src="{{asset('public/site/assets/backend/img/reports-icon.png')}}" alt="" class="img-fluid"> <span>Reports</span>
      </a>
      <div class="dropdown-menu">
        <a class="dropdown-item" href="{{ route('stock-report') }}"><span>Stock Movement Report</span></a>
        <!-- <a class="dropdown-item" href="{{ route('customer-sales-report') }}"> Customer Sales Report</a> -->
        <a class="dropdown-item" href="{{ route('sold-products-report') }}"> Sold Products Report</a>
      </div>
    </li>

    



  </ul>
</nav>
</aside>

<!-- Modal -->
  <div class="modal fade" id="create-customer-quotation" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Choose Customer</h4>
        </div>
        {{-- {{csrf_field()}} --}}
        <div class="modal-body">
       
          <select name="id" class="selectpicker form-control customer customer-select" data-live-search="true" title="Choose Customer">
            @foreach(@$all_customers as $result)
              <option value="{{@$result->id}}">{{@$result->company}}</option>
            @endforeach
          </select>

         <div class="modal-body curr-order-quotation">
         </div>
        

        </div>
        <div class="modal-footer">
          <a href="javascript:void(0);" class="btn create-new-quo" data-action="new">Create New</a>
          <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
        </div>
      </div>
      
    </div>
  </div>