@if(Auth::user()->role_id != 4 && Auth::user()->role_id != 1 && Auth::user()->role_id != 3 && Auth::user()->role_id != 7)
<div class="col-lg col-md-4 pb-md-3 ">

<div class="bg-white box1 pt-4 pb-4 dashboard-boxes-shadow">
  <div class="d-flex align-items-center justify-content-center">
    <img src="{{asset('public/site/assets/sales/img/img1.jpg')}}" class="img-fluid">
    <div class="title pl-2">
      <h6 class="mb-0 headings-color number-size"> {{number_format(@$total_sales,2,'.',',')}}</h6>
        @php  $month = date('M');
            $day = date('d');

       @endphp
      <span class="span-color">My Sales <br> <span>({{@$month}} 1 - {{@$month}} {{@$day}})</span></span>
    </div>
  </div>
</div>

</div>
@endif
@if(Auth::user()->role_id == 1 || Auth::user()->role_id == 3 || Auth::user()->role_id == 7 || Auth::user()->role_id == 11)
<div class="col-lg col-md-4 pb-md-3 ">
  <a  href="{{route('get_total_invoices')}}" title="Total Invoices" class="get_total_invoices">
<div class="bg-white box1 pt-4 pb-4 h-100 dashboard-boxes-shadow">
  <div class="d-flex align-items-center justify-content-center">
    <img src="{{asset('public/site/assets/sales/img/img1.jpg')}}" class="img-fluid">
    <div class="title pl-2">
      <h6 class="mb-0 headings-color number-size"> {{number_format(@$admin_total_sales,2,'.',',')}}</h6>
      @php  $month = date('M');
            $day = date('d');

       @endphp
      <span class="span-color">Total Invoices <br> <span>({{@$month}} 1 - {{@$month}} {{@$day}})</span></span>
    </div>
  </div>
</div>
</a>
</div>
@endif
@if(Auth::user()->role_id == 1 || Auth::user()->role_id == 3 || Auth::user()->role_id == 7 || Auth::user()->role_id == 11)
<div class="col-lg col-md-4 pb-md-3 ">
  <a  href="{{route('get_draft_invoices_dashboard')}}" title="Draft Invoices" class="get_total_invoices">
<div class="bg-white box1 pt-4 pb-4 h-100 dashboard-boxes-shadow">
  <div class="d-flex align-items-center justify-content-center">
    <img src="{{asset('public/site/assets/sales/img/img1.jpg')}}" class="img-fluid">
    <div class="title pl-2">
      <h6 class="mb-0 headings-color number-size"> {{number_format(@$admin_total_sales_draft,2,'.',',')}}</h6>
      @php  $month = date('M');
            $day = date('d');

       @endphp
      <span class="span-color">Draft Invoices <br> <span>(All)</span></span>
    </div>
  </div>
</div>
</a>
</div>

@endif

<div class="col-lg col-md-4 pb-md-3 ">
  <a  href="javascript:void(0)" title="Supplier Invoices" class="get_total_invoices">
<div class="bg-white box1 pt-4 pb-4 h-100 dashboard-boxes-shadow">
  <div class="d-flex align-items-center justify-content-center">
    <img src="{{asset('public/site/assets/sales/img/img1.jpg')}}" class="img-fluid">
    <div class="title pl-2">
      <h6 class="mb-0 headings-color number-size">0</h6>
      @php  $month = date('M');
            $day = date('d');

       @endphp
      <span class="span-color">Supplier Invoices <br> <span>({{@$month}} 1 - {{@$month}} {{@$day}})</span></span>
    </div>
  </div>
</div>
</a>
</div>

<div class="col-lg col-md-4 pb-md-3 ">
  <a  href="javascript:void(0)" title="Gross Margin" class="get_total_invoices">
<div class="bg-white box1 pt-4 pb-4 h-100 dashboard-boxes-shadow">
  <div class="d-flex align-items-center justify-content-center">
    <img src="{{asset('public/site/assets/sales/img/img1.jpg')}}" class="img-fluid">
    <div class="title pl-2">
      <h6 class="mb-0 headings-color number-size">0</h6>
      @php  $month = date('M');
            $day = date('d');

       @endphp
      <span class="span-color">Gross Margin <br> <span>({{@$month}} 1 - {{@$month}} {{@$day}})</span></span>
    </div>
  </div>
</div>
</a>
</div>

<div class="col-lg col-md-4 pb-md-3 ">
  <a  href="{{route('accounting-dashboard')}}" title="Credit Notes" class="get_total_invoices">
<div class="bg-white box1 pt-4 pb-4 h-100 dashboard-boxes-shadow">
  <div class="d-flex align-items-center justify-content-center">
    <img src="{{asset('public/site/assets/sales/img/img1.jpg')}}" class="img-fluid">
    <div class="title pl-2">
      <h6 class="mb-0 headings-color number-size">{{number_format($credit_notes_total,2,'.',',')}}</h6>
      @php  $month = date('M');
            $day = date('d');

       @endphp
      <span class="span-color">Credit Notes <br> <span>({{@$month}} 1 - {{@$month}} {{@$day}})</span></span>
    </div>
  </div>
</div>
</a>
</div>

<div class="col-lg col-md-4 pb-md-3 ">
  <a  href="{{route('debit-notes-dashboard')}}" title="Debit Notes" class="get_total_invoices">
<div class="bg-white box1 pt-4 pb-4 h-100 dashboard-boxes-shadow">
  <div class="d-flex align-items-center justify-content-center">
    <img src="{{asset('public/site/assets/sales/img/img1.jpg')}}" class="img-fluid">
    <div class="title pl-2">
      <h6 class="mb-0 headings-color number-size">{{number_format($debit_notes_total,2,'.',',')}}</h6>
      @php  $month = date('M');
            $day = date('d');

       @endphp
      <span class="span-color">Debit Notes <br> <span>({{@$month}} 1 - {{@$month}} {{@$day}})</span></span>
    </div>
  </div>
</div>
</a>
</div>
