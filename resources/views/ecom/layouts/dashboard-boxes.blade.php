
@if(Auth::user()->role_id == 9 || Auth::user()->role_id == 1 || Auth::user()->role_id == 3 || Auth::user()->role_id == 4 || Auth::user()->role_id == 11)
<div class="col-lg col-md-4 pb-md-3 ">
  <a  href="{{route('ecom-dashboard')}}" title="Total Draft" class="get_total_invoices">
<div class="bg-white box2 d-flex pt-4 pb-4 h-100">
  <div class="d-flex align-items-center justify-content-center w-100">
    <img src="{{asset('public/site/assets/sales/img/img1.jpg')}}" class="img-fluid">
    <div class="title pl-2">
      <h6 class="mb-0 headings-color number-size"> {{number_format(@$admin_total_sales_draft,2,'.',',')}}</h6>
      @php  $month = date('M');
            $day = date('d');

       @endphp
      <span class="span-color">Total Draft <br> <span>(All)</span></span>
    </div>
  </div>
</div>
</a>
</div>
@endif

<div class="col-lg col-md-4 pb-md-3 ">
<a class="my-orders" href="{{route('ecom-dashboard')}}" data-id='7' style="cursor: pointer;" title="My Draft Invoices">
<div class="bg-white box4 d-flex pt-4 pb-4 h-100 dashboard-boxes-shadow">
  <div class="d-flex align-items-center justify-content-center w-100">
    <img src="{{asset('public/site/assets/sales/img/img6.1.jpg')}}" class="img-fluid">
    <div class="title pl-2">
      @if(Auth::user()->role_id == 9 || Auth::user()->role_id == 4)
      <h6 class="mb-0 headings-color number-size">{{@$salesDraft}}</h6>
      @else
       <h6 class="mb-0 headings-color number-size">{{@$salesDraft_ecom}}</h6>
      @endif
      <span class="span-color">@if(!array_key_exists('my_draft_invoice', $global_terminologies)) My Draf Invoices  @else {{$global_terminologies['my_draft_invoice']}} @endif</span>
    </div>
  </div>
</div>
</a>
</div>

<div class="col-lg col-md-4 pb-md-3 ">
  <a class="my-orders" href="{{route('ecom-invoices')}}" data-id='11' style="cursor: pointer;" title="My Invoices">
<div class="bg-white box1 d-flex pt-4 pb-4 dashboard-boxes-shadow">
  <div class="d-flex align-items-center justify-content-center w-100">
    <img src="{{asset('public/site/assets/sales/img/img1.jpg')}}" class="img-fluid">
    <div class="title pl-2">
      <h6 class="mb-0 headings-color number-size"> {{number_format(@$admin_total_invoices,2,'.',',')}}</h6>
        @php  $month = date('M');
            $day = date('d');

       @endphp
         <span class="span-color">Total Invoices <br> <span>(All)</span></span>
      {{--<span class="span-color">Total Invoices <br> <span>({{@$month}} 1 - {{@$month}} {{@$day}})</span></span>--}}
    </div>
  </div>
</div>
</a>
</div>

<div class="col-lg col-md-4 pb-md-3 ">
<a class="my-orders" href="{{route('ecom-invoices')}}" data-id='11' style="cursor: pointer;" title="My Invoices">
<div class="bg-white box5 d-flex pt-4 pb-4 h-100 dashboard-boxes-shadow">
  <div class="d-flex align-items-center justify-content-center w-100">
    <img src="{{asset('public/site/assets/sales/img/img7.1.jpg')}}" class="img-fluid">
    <div class="title pl-2">
      @if(Auth::user()->role_id == 9 || Auth::user()->role_id == 4)
      <h6 class="mb-0 headings-color number-size">{{@$Invoice1}}</h6>
      @else
      <h6 class="mb-0 headings-color number-size">{{@$Invoice_ecom}}</h6>
      @endif
      <span class="span-color">@if(!array_key_exists('my_invoice', $global_terminologies)) My Invoices  @else {{$global_terminologies['my_invoice']}} @endif</span>
    </div>
  </div>
</div>
</a>
</div>
