@php
  $config = \App\Models\Common\Configuration::first(); 
  $role_id = @auth()->user()->role_id;
@endphp
@if(Auth::user()->role_id == 1 || Auth::user()->role_id == 11)
<div class="col-lg col-md-6 pb-md-3 phone-boxes-size">
<a class="my-orders" href="{{url('sales/')}}" data-id='6' style="cursor: pointer;" title="My Quotations">
<div class="bg-white box3 pt-4 pb-4 h-100 dashboard-boxes-shadow phone-boxes">
  <div class="d-flex align-items-center justify-content-center w-100 mt-3 phone-boxes-title-margin">
    <span class="span-color font-weight-bold dashboard-boxes-font">@if(!array_key_exists('my_quotation', $global_terminologies)) My Quotations: @else {{$global_terminologies['my_quotation']}}: @endif</span>
  </div>
  <div class="d-flex align-items-center justify-content-center w-100 dashboard-boxes-font">
    <h6 class="mb-0 headings-color admin-quotation-count font-weight-bold pr-2 dashboard-boxes-font">{{@$quotation}}</h6>
    @if((@$config->server == 'lucilla' && $role_id == 1 ) || @$config->server != 'lucilla' )
      (<img src="{{asset('public/site/assets/sales/img/img1.jpg')}}" class="img-fluid pr-2 dashboard-boxes-img">
      <h6 class="mb-0 headings-color total_amount_of_quotation_admin dashboard-boxes-font">
      {{number_format(@$total_amount_of_quotation,2,'.',',')}}</h6>)
    @endif
  </div>

</div>
</a>
</div>


<div class="col-lg col-md-6 pb-md-3 phone-boxes-size">
  <a  href="{{route('get_total_draft')}}" title="My Draft Invoices" class="get_total_invoices">
<div class="bg-white box2 pt-4 pb-4 h-100 dashboard-boxes-shadow phone-boxes">
  <div class="d-flex align-items-center justify-content-center w-100 mt-3 phone-boxes-title-margin">
    <span class="span-color font-weight-bold dashboard-boxes-font">Total Draft Invoices: </span>
  </div>
  <div class="d-flex align-items-center justify-content-center w-100 dashboard-boxes-font">
    <h6 class="mb-0 headings-color total_number_of_draft_invoices_admin pr-2 font-weight-bold dashboard-boxes-font">{{number_format(@$total_number_of_draft_invoices,0,'.',',')}}</h6>
    @if((@$config->server == 'lucilla' && $role_id == 1 ) || @$config->server != 'lucilla' )
    (<img src="{{asset('public/site/assets/sales/img/img1.jpg')}}" class="img-fluid pr-2 dashboard-boxes-img">
      <h6 class="mb-0 headings-color admin_total_sales_draft_admin dashboard-boxes-font"> {{number_format(@$admin_total_sales_draft,2,'.',',')}}</h6>)
    @endif
      @php  $month = date('M');
            $day = date('d');

       @endphp
  </div>

</div>
</a>
</div>

<div class="col-lg col-md-6 pb-md-3 phone-boxes-size">
  <a  href="{{route('get_total_invoices')}}" title="Total Invoices" class="get_total_invoices">
<div class="bg-white box1 pt-4 pb-4 h-100 dashboard-boxes-shadow phone-boxes">
  <div class="d-flex align-items-center justify-content-center w-100 mt-3 phone-boxes-title-margin">
    @php  $month = date('M');
          $day = date('d');

     @endphp
    <span class="span-color font-weight-bold dashboard-boxes-font">Total Invoices: </span><span class="pl-2 dashboard-boxes-bracket-text-font span-color">({{@$month}} 1 - {{@$month}} {{@$day}})</span>
  </div>
  <div class="d-flex align-items-center justify-content-center w-100 dashboard-boxes-font">
    <h6 class="mb-0 headings-color total_number_of_invoices_admin pr-2 font-weight-bold dashboard-boxes-font">{{number_format(@$total_number_of_invoices,0,'.',',')}}</h6>

    @if((@$config->server == 'lucilla' && $role_id == 1 ) || @$config->server != 'lucilla' )
    (<img src="{{asset('public/site/assets/sales/img/img1.jpg')}}" class="img-fluid pr-2 dashboard-boxes-img">
      <h6 class="mb-0 headings-color admin_total_sales dashboard-boxes-font"> {{number_format(@$admin_total_sales,2,'.',',')}}</h6>)
      @endif
  </div>
</div>
</a>
</div>

<div class="col-lg col-md-6 pb-md-3 phone-boxes-size">
  <a  href="{{url('margin-report-2/true')}}" title="Total Outstanding" class="total_gross_weight">
<div class="bg-white box5 pt-4 pb-4 h-100 dashboard-boxes-shadow phone-boxes">
  <div class="d-flex align-items-center justify-content-center w-100 mt-3 phone-boxes-title-margin">
    <span class="span-color pr-2 font-weight-bold dashboard-boxes-font">Total Outstanding: </span><span class="dashboard-boxes-bracket-text-font span-color">(Invoices)</span>
  </div>
  <div class="d-flex align-items-center justify-content-center w-100">
      <h6 class="mb-0 headings-color total_gross_profit_count font-weight-bold pr-2 dashboard-boxes-font">{{number_format(@$total_gross_profit_count,0,'.',',')}}</h6>
    @if((@$config->server == 'lucilla' && $role_id == 1 ) || @$config->server != 'lucilla' )
      <span class="span-color font-weight-bold dashboard-boxes-font">(</span><img src="{{asset('public/site/assets/sales/img/img1.jpg')}}" class="img-fluid pr-2 dashboard-boxes-img">
        <h6 class="mb-0 headings-color total_gross_profit dashboard-boxes-font"> {{number_format(@$total_gross_profit,2,'.',',')}}</h6><span class="span-color font-weight-bold dashboard-boxes-font">)</span>
    @endif
      <!-- <span class="span-color">Total Outstanding <br> <span>({{@$month}} 1 - {{@$month}} {{@$day}})</span></span> -->
  </div>
</div>
</a>
</div>

<div class="col-lg col-md-6 pb-md-3 phone-boxes-size">
  <a  href="{{route('account-recievable')}}" title="Total Overdue" class="total_overdue">
<div class="bg-white box4 pt-4 pb-4 h-100 dashboard-boxes-shadow phone-boxes">
  <div class="d-flex align-items-center justify-content-center w-100 mt-3 phone-boxes-title-margin">
    <span class="span-color font-weight-bold dashboard-boxes-font">Total Overdue</span>
  </div>
  <div class="d-flex align-items-center justify-content-center w-100">
    <h6 class="mb-0 headings-color total_amount_of_overdue_invoices_count font-weight-bold pr-2 dashboard-boxes-font">{{number_format(@$total_amount_of_overdue_invoices_count,0,'.',',')}}</h6>
    @if((@$config->server == 'lucilla' && $role_id == 1 ) || @$config->server != 'lucilla' )
    <span class="span-color font-weight-bold dashboard-boxes-font">(</span><img src="{{asset('public/site/assets/sales/img/img1.jpg')}}" class="img-fluid pr-2 dashboard-boxes-img">
      <h6 class="mb-0 headings-color total_amount_overdue dashboard-boxes-font"> {{number_format(@$total_amount_overdue,2,'.',',')}}</h6><span class="span-color font-weight-bold dashboard-boxes-font">)</span>
    @endif
  </div>
</div>
</a>
</div>
<!-- admin ends -->
@else
@if(Auth::user()->role_id == 3 && @$config->server != 'lucilla')
<div class="col-lg col-md-6 pb-md-3 phone-boxes-size">
<div class="bg-white box1 pt-4 pb-4 h-100 dashboard-boxes-shadow phone-boxes">
  <div class="d-flex align-items-center justify-content-center w-100 mt-3 phone-boxes-title-margin">
      @php  $month = date('M');
          $day = date('d');

     @endphp
      <span class="span-color dashboard-boxes-bracket-text-font font-weight-bold">Total Invoices:</span>
      <span class="span-color pl-2 dashboard-boxes-bracket-text-font">({{@$month}} 1 - {{@$month}} {{@$day}})</span>
  </div>
  <div class="d-flex align-items-center justify-content-center w-100">
    @if((@$config->server == 'lucilla' && $role_id == 1 ) || @$config->server != 'lucilla' )
    <span class="span-color font-weight-bold dashboard-boxes-bracket-text-font">(</span>
    <img src="{{asset('public/site/assets/sales/img/img1.jpg')}}" class="img-fluid pr-2 dashboard-boxes-img">
    <h6 class="mb-0 headings-color company_total_sales dashboard-boxes-bracket-text-font"> {{number_format(@$company_total_sales,2,'.',',')}}</h6>
    <span class="span-color font-weight-bold dashboard-boxes-bracket-text-font">)</span>
    @endif
  </div>
</div>
</div>
@endif
@if(Auth::user()->role_id == 1 || Auth::user()->role_id == 3 || Auth::user()->role_id == 7 || Auth::user()->role_id == 8 || Auth::user()->role_id == 11)
<div class="col-lg col-md-6 pb-md-3 phone-boxes-size">
  <a  href="{{route('get_total_invoices')}}" title="Total Invoices" class="get_total_invoices">
<div class="bg-white box1 pt-4 pb-4 h-100 dashboard-boxes-shadow phone-boxes">
  <div class="d-flex align-items-center justify-content-center w-100 mt-3 phone-boxes-title-margin">
    @php  $month = date('M');
          $day = date('d');

     @endphp
     @if(Auth::user()->role_id !== 3)
    <span class="span-color dashboard-boxes-font font-weight-bold">Total Invoices:</span>
    @else
    <span class="span-color dashboard-boxes-bracket-text-font font-weight-bold">My Invoices:</span>
    @endif
    <span class="span-color pl-2 dashboard-boxes-bracket-text-font">({{@$month}} 1 - {{@$month}} {{@$day}})</span>
  </div>
  <div class="d-flex align-items-center justify-content-center w-100">
    @if((@$config->server == 'lucilla' && $role_id == 1 ) || @$config->server != 'lucilla' )
    <span class="span-color font-weight-bold dashboard-boxes-font">(</span>
    <img src="{{asset('public/site/assets/sales/img/img1.jpg')}}" class="img-fluid pr-2 dashboard-boxes-img">
    <h6 class="mb-0 headings-color admin_total_sales dashboard-boxes-bracket-text-font"> {{number_format(@$admin_total_sales,2,'.',',')}}</h6>
    <!-- <h6 class="mb-0 headings-color dashboard-boxes-bracket-text-font salesInvoice">{{@$salesInvoice}}</h6> -->
    <span class="span-color font-weight-bold dashboard-boxes-bracket-text-font">)</span>
    @endif
  </div>

  <div class="d-flex align-items-center justify-content-center w-100">
    <span class="span-color font-weight-bold dashboard-boxes-font">(</span>
    <h6 class="mb-0 headings-color dashboard-boxes-bracket-text-font salesInvoice">{{@$salesInvoice}}</h6>
    <span class="span-color font-weight-bold dashboard-boxes-bracket-text-font">)</span>
  </div>

</div>
</a>
</div>
@endif
@if(Auth::user()->role_id == 1 || Auth::user()->role_id == 3 || Auth::user()->role_id == 7 || Auth::user()->role_id == 8 || Auth::user()->role_id == 11)
<div class="col-lg col-md-6 pb-md-3 phone-boxes-size">
  <a  href="{{route('get_total_draft')}}" title="My Draft Invoices" class="get_total_invoices">
<div class="bg-white box2 pt-4 pb-4 h-100 dashboard-boxes-shadow phone-boxes">
  <div class="d-flex align-items-center justify-content-center w-100 mt-3 phone-boxes-title-margin">
    <span class="span-color font-weight-bold dashboard-boxes-bracket-text-font">Total Draft Invoices:</span>
  </div>
  <div class="d-flex align-items-center justify-content-center w-100">
    @if((@$config->server == 'lucilla' && $role_id == 1 ) || @$config->server != 'lucilla' )
    <span class="font-weight-bold span-color dashboard-boxes-bracket-text-font">(</span>
    <img src="{{asset('public/site/assets/sales/img/img1.jpg')}}" class="img-fluid pr-2 dashboard-boxes-img">
      <h6 class="mb-0 headings-color admin_total_sales_draft_admin dashboard-boxes-bracket-text-font"> {{number_format(@$admin_total_sales_draft,2,'.',',')}}</h6>
      <span class="span-color font-weight-bold dashboard-boxes-bracket-text-font">)</span>
    @endif
      @php  $month = date('M');
            $day = date('d');

       @endphp
  </div>

  <div class="d-flex align-items-center justify-content-center w-100">
    <span class="font-weight-bold span-color dashboard-boxes-bracket-text-font">(</span>
    <h6 class="mb-0 headings-color dashboard-boxes-bracket-text-font salesDraft">{{@$salesDraft}}</h6>
      <span class="span-color font-weight-bold dashboard-boxes-bracket-text-font">)</span>
  </div>

</div>
</a>
</div>

@endif
@if(Auth::user()->role_id == 3)
<div class="col-lg col-md-6 pb-md-3 phone-boxes-size">
<a  href="{{route('list-customer')}}" title="Customers Center">
<div class="bg-white box2 pt-4 pb-4 h-100 dashboard-boxes-shadow phone-boxes">
  <div class="d-flex align-items-center justify-content-center w-100 mt-3 phone-boxes-title-margin">
    <span class="span-color font-weight-bold dashboard-boxes-bracket-text-font">@if(!array_key_exists('my_clients', $global_terminologies)) My Clients: @else {{$global_terminologies['my_clients']}}: @endif</span>
  </div>
  <div class="d-flex align-items-center justify-content-center w-100">
    <span class="span-color font-weight-bold dashboard-boxes-bracket-text-font">(</span>
    <img src="{{asset('public/site/assets/sales/img/img2.jpg')}}" class="img-fluid pr-2 dashboard-boxes-img">
      @if(Auth::user()->role_id == 3)
      <h6 class="mb-0 headings-color salesCustomers dashboard-boxes-bracket-text-font">{{@$salesCustomers}}</h6>
      @else
      <h6 class="mb-0 headings-color totalCustomers dashboard-boxes-bracket-text-font">{{@$totalCustomers}}</h6>
      @endif
    <span class="span-color font-weight-bold dashboard-boxes-bracket-text-font">)</span>
  </div>
</div>
</a>
</div>
@elseif(Auth::user()->role_id == 4)
<div class="col-lg col-md-6 pb-md-3 phone-boxes-size">
<a  href="{{ route('list-customer') }}" title="Customers Center">
<div class="bg-white box2 pt-4 pb-4 h-100 dashboard-boxes-shadow phone-boxes">
  <div class="d-flex align-items-center justify-content-center w-100 mt-3 phone-boxes-title-margin">
      <span class="span-color font-weight-bold dashboard-boxes-bracket-text-font">@if(!array_key_exists('my_clients', $global_terminologies)) My Clients: @else {{$global_terminologies['my_clients']}}: @endif</span>
  </div>
  <div class="d-flex align-items-center justify-content-center w-100">
    <span class="span-color font-weight-bold dashboard-boxes-bracket-text-font">(</span>
    <img src="{{asset('public/site/assets/sales/img/img2.jpg')}}" class="img-fluid pr-2 dashboard-boxes-img">
      <h6 class="mb-0 headings-color sales_coordinator_customers_count dashboard-boxes-bracket-text-font"> {{@$sales_coordinator_customers_count}} </h6>
    <span class="span-color font-weight-bold dashboard-boxes-bracket-text-font">)</span>
  </div>
</div>
</a>
</div>
@endif

<div class="col-lg col-md-6 pb-md-3 phone-boxes-size">
<a class="my-orders" href="{{url('sales/')}}" data-id='6' style="cursor: pointer;" title="My Quotations">
<div class="bg-white box3 pt-4 pb-4 h-100 dashboard-boxes-shadow phone-boxes">
  <div class="d-flex align-items-center justify-content-center w-100 mt-3 phone-boxes-title-margin">
    <span class="span-color font-weight-bold dashboard-boxes-bracket-text-font">@if(!array_key_exists('my_quotation', $global_terminologies)) My Quotations:  @else {{$global_terminologies['my_quotation']}}: @endif</span>
  </div>
  <div class="d-flex align-items-center justify-content-center w-100">
    <span class="span-color font-weight-bold dashboard-boxes-bracket-text-font">(</span>
      @if(Auth::user()->role_id == 3)
      <h6 class="mb-0 headings-color dashboard-boxes-bracket-text-font salesQuotations">{{@$salesQuotations}}</h6>
      @elseif(Auth::user()->role_id == 1 || Auth::user()->role_id == 7 || Auth::user()->role_id == 11)
      <h6 class="mb-0 headings-color dashboard-boxes-bracket-text-font admin-quotation-count">{{@$quotation}}</h6>
      @else
      <h6 class="mb-0 headings-color dashboard-boxes-bracket-text-font salesCoordinateQuotations">{{@$salesCoordinateQuotations}}</h6>
      @endif
      <span class="span-color font-weight-bold dashboard-boxes-bracket-text-font">)</span>
  </div>
</div>
</a>
</div>

@if(Auth::user()->role_id !== 3)
<div class="col-lg col-md-6 pb-md-3 phone-boxes-size">
<a class="my-orders" href="{{url('sales/draft_invoices')}}" data-id='7' style="cursor: pointer;" title="My Draft Invoices">
<div class="bg-white box4 pt-4 pb-4 h-100 dashboard-boxes-shadow phone-boxes">
  <div class="d-flex align-items-center justify-content-center w-100 mt-3 phone-boxes-title-margin">
    <span class="span-color font-weight-bold dashboard-boxes-bracket-text-font">@if(!array_key_exists('my_draft_invoice', $global_terminologies)) My Draf Invoices:  @else {{$global_terminologies['my_draft_invoice']}}: @endif</span>
  </div>
  <div class="d-flex align-items-center justify-content-center w-100">
    <span class="span-color font-weight-bold dashboard-boxes-bracket-text-font">(</span>
      @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 7 || Auth::user()->role_id == 11)
      <h6 class="mb-0 headings-color dashboard-boxes-bracket-text-font salesDraft">{{@$salesDraft}}</h6>
      @else
      <h6 class="mb-0 headings-color dashboard-boxes-bracket-text-font salesCoordinateDraftInvoices">{{@$salesCoordinateDraftInvoices}}</h6>
      @endif
    <span class="span-color font-weight-bold dashboard-boxes-bracket-text-font">)</span>
  </div>
</div>
</a>
</div>
@endif

@if(Auth::user()->role_id !== 3)
<div class="col-lg col-md-6 pb-md-3 phone-boxes-size">
<a class="my-orders" href="{{url('sales/invoices')}}" data-id='11' style="cursor: pointer;" title="My Invoices">
<div class="bg-white box5 pt-4 pb-4 h-100 dashboard-boxes-shadow phone-boxes">
  <div class="d-flex align-items-center justify-content-center w-100 mt-3 phone-boxes-title-margin">
    <span class="span-color font-weight-bold dashboard-boxes-bracket-text-font">@if(!array_key_exists('my_invoice', $global_terminologies)) My Invoices: @else {{$global_terminologies['my_invoice']}}: @endif
    </span>
    <span class="span-color dashboard-boxes-bracket-text-font pl-2">
    @if(Auth::user()->role_id == 3)
    ({{$month}} 1 - {{$month}} {{$day}})
    @elseif(Auth::user()->role_id == 4)
     @php  $month = date('M');
          $day = date('d');
     @endphp
     ({{$month}} 1 - {{$month}} {{$day}})
    @endif
    </span>
  </div>
  <div class="d-flex align-items-center justify-content-center w-100">
    <span class="span-color font-weight-bold dashboard-boxes-bracket-text-font">(</span>
    @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 7 || Auth::user()->role_id == 11)
      <h6 class="mb-0 headings-color dashboard-boxes-bracket-text-font invoice1">{{@$Invoice1}}</h6>
      @else
      <h6 class="mb-0 headings-color dashboard-boxes-bracket-text-font salesCoordinateInvoices">{{@$salesCoordinateInvoices}}</h6>
      @endif
    <span class="span-color font-weight-bold dashboard-boxes-bracket-text-font">)</span>
  </div>
</div>
</a>
</div>
@endif

<!-- new added for others -->
<div class="col-lg col-md-6 pb-md-3 d-none">
<a class="my-orders" href="{{url('sales/others')}}" data-id='11' style="cursor: pointer;" title="Other">
<div class="bg-white box6 d-flex pt-4 pb-4 h-100">
  <div class="d-flex align-items-center justify-content-center w-100">
    <img src="{{asset('public/site/assets/sales/img/img7.1.jpg')}}" class="img-fluid">
    <div class="title pl-2">
      @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 7 || Auth::user()->role_id == 8 || Auth::user()->role_id == 11)
      <h6 class="mb-0 headings-color number-size">{{@$Other1}}</h6>
      @elseif(Auth::user()->role_id == 3)
      <h6 class="mb-0 headings-color number-size">{{@$salesOther}}</h6>
      @else
      <h6 class="mb-0 headings-color number-size">{{@$salesCoordinateOthers}}</h6>
      @endif
      <span class="span-color">Other</span>
    </div>
  </div>
</div>
</a>
</div>
@endif
