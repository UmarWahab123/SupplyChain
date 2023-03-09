<div class="col ">
<div class="bg-white box1 pt-4 pb-4">
  <div class="d-flex align-items-center justify-content-center">
    <img src="{{asset('public/site/assets/sales/img/img1.jpg')}}" class="img-fluid">
    <div class="title pl-2">
      <h6 class="mb-0 headings-color number-size">{{number_format($total_sales,2,'.',',')}}</h6>
      <span class="span-color">My Sales</span>
    </div>
  </div>
</div>
</div>

<div class="col ">
<a  href="{{route('list-customer')}}" title="Customers Center">
<div class="bg-white box2 pt-4 pb-4">
  <div class="d-flex align-items-center justify-content-center">
    <img src="{{asset('public/site/assets/sales/img/img2.jpg')}}" class="img-fluid">
    <div class="title pl-2">
      <h6 class="mb-0 headings-color number-size">{{$totalCustomers}}</h6>
      <span class="span-color"> @if(!array_key_exists('my_clients', $global_terminologies)) My Clients @else {{$global_terminologies['my_clients']}} @endif</span>
    </div>
  </div>
</div>
</a>
</div>
<div class="col ">
<a class="my-orders" href="{{url('admin/sales_dashboard')}}" data-id='6' style="cursor: pointer;" title="My Quotations">
<div class="bg-white box3 pt-4 pb-4">
  <div class="d-flex align-items-center justify-content-center">
    <img src="{{asset('public/site/assets/sales/img/img5.1.jpg')}}" class="img-fluid">
    <div class="title pl-2">
      <h6 class="mb-0 headings-color number-size">{{@$quotation}}</h6>
      <span class="span-color">My Quotations</span>
    </div>
  </div>
</div>
</a>
</div>

<div class="col ">
<a class="my-orders" href="{{url('admin/admin_draft_invoices')}}" data-id='7' style="cursor: pointer;" title="My Draft Invoices">  
<div class="bg-white box4 pt-4 pb-4">
  <div class="d-flex align-items-center justify-content-center">
    <img src="{{asset('public/site/assets/sales/img/img6.1.jpg')}}" class="img-fluid">
    <div class="title pl-2">
      <h6 class="mb-0 headings-color number-size">{{@$salesDraft}}</h6>
      <span class="span-color">My Draft Invoices</span>
    </div>
  </div>
</div>
</a>
</div>

<div class="col ">
<a class="my-orders" href="{{url('admin/admin_invoices')}}" data-id='11' style="cursor: pointer;" title="My Invoices">  
<div class="bg-white box5 pt-4 pb-4">
  <div class="d-flex align-items-center justify-content-center">
    <img src="{{asset('public/site/assets/sales/img/img7.1.jpg')}}" class="img-fluid">
    <div class="title pl-2">
      <h6 class="mb-0 headings-color number-size">{{@$Invoice1}}</h6>
      <span class="span-color">My Invoices</span>
    </div>
  </div>
</div>
</a>
</div>