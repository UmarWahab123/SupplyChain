@extends('backend.layouts.layout')
@section('title','Dashboard')


@section('content')

{{-- Content Start from here --}}

<!-- Overview Row -->
<!-- <div class="row justify-content-center">
	<h2 align="center">Welcome Admin</h2>
</div> -->

<!-- Right Content Start Here -->

<div class="right-content pt-0 mt-0 ">
<h3>Dashboard</h3>
<!-- upper section start -->
<div class="row mb-3">
<!-- left Side Start -->

<div class="col-lg-9 col-md-12">
<!-- 1st four box row start -->
<div class="row mb-3">

<div class="col-lg-3 col-md-5 pb-3 ">
<a href="{{route('list-customer')}}" title="Customers Center">
  <div class="bg-white box1 pt-4 pb-4">
    <div class="d-flex align-items-center justify-content-center">
      <img src="{{asset('public/img/total-customers-icon.png')}}" class="img-fluid">
      <div class="title pl-3">
        <h5 class="mb-0 headings-color"> {{@$customers}}</h5>
        <span class="span-color">Total Customers</span>
      </div>
    </div>
  </div>
</a>
</div>

<div class="col-lg-3 col-md-5 pb-3">
<a href="{{ route('list-of-suppliers') }}" title="Total Supplier">
  <div class="bg-white box2 pt-4 pb-4">
    <div class="d-flex align-items-center justify-content-center">
      <img src="{{asset('public/img/total-supplier-icon.png')}}" class="img-fluid">
      <div class="title pl-3">
        <h5 class="mb-0 headings-color"> {{@$suppliers}} </h5>
        <span class="span-color">Total Supplier</span>
      </div>
    </div>
  </div>
</a>
</div>

<div class="col-lg-3 col-md-5 pb-3">
<a href="{{ route('complete-list-product') }}" title="Total Products">
  <div class="bg-white box3 pt-4 pb-4">
    <div class="d-flex align-items-center justify-content-center">
      <img src="{{asset('public/img/total-products-icon.png')}}" class="img-fluid">
      <div class="title pl-3">
        <h5 class="mb-0 headings-color"> {{@$products}} </h5>
        <span class="span-color">Total Products</span>
      </div>
    </div>
  </div>
</a>
</div>

<div class="col-lg-3 col-md-5 pb-3">
<a href="{{route('all-users-list')}}" title="Users">
  <div class="bg-white box4 pt-4 pb-4">
    <div class="d-flex align-items-center justify-content-center">
      <img src="{{asset('public/img/users-icon.png')}}" class="img-fluid">
      <div class="title pl-3">
        <h5 class="mb-0 headings-color"> {{@$users}} </h5>
        <span class="span-color">Users</span>
      </div>
    </div>
  </div>
</a>
</div>
</div>
<!-- first four box row end-->


<!-- 2nd row start-->
<div class="row">
  <div class="col-lg-6 col-md-6">
    <div class="row">

      <div class="col-lg-6 col-md-6 text-center mb-3">
        <a href="{{url('sales/')}}" title="Quotations">
          <div class="bg-white innerbox">
           <figure class="align-items-center d-flex justify-content-center mb-2">
             <img src="{{asset('public/img/quotation-icon.png')}}" class="img-fluid">
           </figure>
           <h5 class="mb-0 headings-color">Quotations</h5>
           <span class="span-color">{{$quotation}}</span>
          </div>
        </a>
      </div>


      <div class="col-lg-6 col-md-6 text-center">
        <a href="{{url('sales/draft_invoices')}}" title="Drafts">
          <div class="bg-white innerbox">
           <figure class="align-items-center d-flex justify-content-center mb-2">
             <img src="{{asset('public/img/draft-icon.png')}}" class="img-fluid">
           </figure>
           <h5 class="mb-0 headings-color">Drafts</h5>
           <span class="span-color">{{$salesDraft}}</span>
          </div>
        </a>
      </div>


      <div class="col-lg-6 col-md-6 text-center">
        <a href="javascript:void(0)" title="Total Orders">
        <div class="bg-white innerbox">
       <figure class="align-items-center d-flex justify-content-center mb-2">
         <img src="{{asset('public/img/orders-icon.png')}}" class="img-fluid">
       </figure>
       <h5 class="mb-0 headings-color">Total Orders</h5>
       <span class="span-color"> {{@$orders}} </span>
      </div>
    </a>
      </div>



      <div class="col-lg-6 col-md-6 text-center">
        <a href="{{url('sales/invoices')}}" title="Invoices">
          <div class="bg-white innerbox">
           <figure class="align-items-center d-flex justify-content-center mb-2">
             <img src="{{asset('public/img/invoice-icon.png')}}" class="img-fluid">
           </figure>
           <h5 class="mb-0 headings-color">Invoices</h5>
           <span class="span-color">{{@$Invoice1}}</span>
          </div>
        </a>
      </div>





    </div>
  </div>


  <div class="col-lg-6 d-none">
    <div class="bg-white graph">
      <div class="d-flex justify-content-between align-items-center">
        <div>
        <h5 class="headings-color mb-0">Daily Sales</h5>
        <p class="span-color mb-0">Check out each collumn for more details</p>
      </div>
        <span class="today-color text-center">Today</span>
      </div>
      <img src="{{asset('public/img/graph.jpg')}}" class="img-fluid pt-5">
    </div>
  </div>

</div>
<!-- 2nd row end-->
</div>

<!-- left Side End -->





<!-- Today's Deliveries Start -->

<div class="col-lg-3 d-none">
  <div class="bg-white pt-3 pl-3">
  <h6 class="today-table-tr pb-2 mb-0 headings-color">Today's Deliveries</h6>
  <!-- <table class="table today-delivery my-table" > -->
  <table class="today-delivery my-table" >

      <thead class="customer-color">
        <tr>
          <th style="width: 1px;">#</th>
        <th>Customer Name</th>
        <th class="text-center">Order #</th>
        </tr>                         
      </thead>
      <tbody class="todays-table-color">
        @if($d_orders->count() > 0)
          @foreach($d_orders as $order)
          <tr class="today-table-tr">
            <td><span>1</span></td>
            <td> {{@$order->customer->reference_name}} </td>
            <td class="text-center"> {{$order->ref_id}} </td>
          </tr>
          @endforeach
        @else
          <tr>
            <td colspan="3" style="text-align: center;"> Data not found </td>
          </tr>
        @endif
      </tbody>
  </table>
</div>
</div>

<!-- Today's Deliveries End -->

</div>

<!-- upper section end  -->


<!-- lower section start -->
<div class="row d-none">

 <!-- col 4(1) start--> <div class="col-lg-4">
    <div class="bg-white pt-4 h-100">
    <div class="row pl-4 align-items-center best-seller position-relative">
      <div class="col-lg-4">
        <h5 class="mb-0 pt-2 headings-color">Best Sellers</h5>
      </div>
      <div class="col-lg-8">
  <!--     <div class="card mt-3 tab-card"> -->
        <!-- <div class="card-header tab-card-header"> -->
          <!-- <ul class="nav nav-tabs card-header-tabs pull-right pr-3 span-color" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="one-tab" data-toggle="tab" href="#one" role="tab" aria-controls="One" aria-selected="true">Latest</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="two-tab" data-toggle="tab" href="#two" role="tab" aria-controls="Two" aria-selected="false">Month</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="three-tab" data-toggle="tab" href="#three" role="tab" aria-controls="Three" aria-selected="false">All Time</a>
            </li>
          </ul> -->
        </div>
      </div>

<div class="tab-content" id="myTabContent">
  <div class="tab-pane fade show active p-3" id="one" role="tabpanel" aria-labelledby="one-tab">
        @foreach(@$sellers_array as $seller)

          <div class="row dot-dash align-items-center pt-4">
            <div class="col-lg-3">
               <div class="d-flex align-items-center justify-content-center">
                <figure class="d-flex align-items-center justify-content-center rounded-circle ml-auto mr-auto bg-img">
                      <img src="{{asset('public/img/profileImg.jpg')}}" class="img-fluid mx-auto d-block">
                </figure>

            </div>
          </div>
            <div class="col pl-0">
              <h6 class="headings-color">{{@$seller['name']}}</h6>
              <!-- <p class="text-nowrap span-color">Supplier: Keenthemes Released: 23.08.17</p>
              <p class=" span-color">Supplier: Keenthemes Released: 23.08.17</p> -->
            </div>
            <div class="col">
              <h6 class="mb-0 headings-color"> {{@$seller['total_sale']}} </h6>
              <span class="span-color">Sales</span>
            </div>

          </div>
        @endforeach


        

      </div>


  <div class="tab-pane fade p-3" id="two" role="tabpanel" aria-labelledby="one-tab">

    <div class="row dot-dash align-items-center pt-4">
          <div class="col-lg-3">
             <div class="d-flex align-items-center justify-content-center">
              <figure class="d-flex align-items-center justify-content-center rounded-circle ml-auto mr-auto bg-img">
                    <img src="{{asset('public/img/profileImg.jpg')}}" class="img-fluid mx-auto d-block">
              </figure>

  </div>
          </div>
         <div class="col-lg-7 pl-0">
            <h6 class="headings-color">Lorem Ipsum available</h6>
            <p class=" span-color">Supplier: Keenthemes Released: 23.08.17</p>
          </div>
          <div class="col-lg-2">
            <h6 class="mb-0 headings-color">19,200</h6>
            <span class="span-color">Sales</span>
          </div>

        </div>


        <div class="row dot-dash align-items-center pt-3">
          <div class="col-lg-3">
             <div class="d-flex align-items-center justify-content-center">
              <figure class="d-flex align-items-center justify-content-center rounded-circle ml-auto mr-auto bg-img">
                    <img src="{{asset('public/img/profileImg.jpg')}}" class="img-fluid mx-auto d-block">
              </figure>

  </div>
          </div>
       <div class="col-lg-7 pl-0">
            <h6 class="headings-color">Lorem Ipsum available</h6>
            <p class=" span-color">Supplier: Keenthemes Released: 23.08.17</p>
          </div>
          <div class="col-lg-2">
            <h6 class="mb-0 headings-color">19,200</h6>
            <span class="span-color">Sales</span>
          </div>

        </div>


        <div class="row dot-dash align-items-center pt-3">
         <div class="col-lg-3">
             <div class="d-flex align-items-center justify-content-center">
              <figure class="d-flex align-items-center justify-content-center rounded-circle ml-auto mr-auto bg-img">
                    <img src="{{asset('public/img/profileImg.jpg')}}" class="img-fluid mx-auto d-block">
              </figure>

  </div>
          </div>
         <div class="col-lg-7 pl-0">
            <h6 class="headings-color">Lorem Ipsum available</h6>
            <p class=" span-color">Supplier: Keenthemes Released: 23.08.17</p>
          </div>
          <div class="col-lg-2">
            <h6 class="mb-0 headings-color">19,200</h6>
            <span class="span-color">Sales</span>
          </div>

        </div>

        <div class="row align-items-center pt-3">
          <div class="col-lg-3">
             <div class="d-flex align-items-center justify-content-center">
              <figure class="d-flex align-items-center justify-content-center rounded-circle ml-auto mr-auto bg-img">
                    <img src="{{asset('public/img/profileImg.jpg')}}" class="img-fluid mx-auto d-block">
              </figure>

  </div>
          </div>
      <div class="col-lg-7 pl-0">
            <h6 class="headings-color">Lorem Ipsum available</h6>
            <p class=" span-color">Supplier: Keenthemes Released: 23.08.17</p>
          </div>
          <div class="col-lg-2">
            <h6 class="mb-0 headings-color">19,200</h6>
            <span class="span-color">Sales</span>
          </div>

        </div>
      </div>


        <div class="tab-pane fade p-3" id="three" role="tabpanel" aria-labelledby="one-tab">
<div class="row dot-dash align-items-center PT-4">
          <div class="col-lg-3">
             <div class="d-flex align-items-center justify-content-center">
              <figure class="d-flex align-items-center justify-content-center rounded-circle ml-auto mr-auto bg-img">
                    <img src="{{asset('public/img/profileImg.jpg')}}" class="img-fluid mx-auto d-block">
              </figure>

  </div>
          </div>
       <div class="col-lg-7 pl-0">
            <h6 class="headings-color">Lorem Ipsum available</h6>
            <p class=" span-color">Supplier: Keenthemes Released: 23.08.17</p>
          </div>
          <div class="col-lg-2">
            <h6 class="mb-0 headings-color">19,200</h6>
            <span class="span-color">Sales</span>
          </div>

        </div>


        <div class="row dot-dash align-items-center pt-3">
          <div class="col-lg-3">
             <div class="d-flex align-items-center justify-content-center">
              <figure class="d-flex align-items-center justify-content-center rounded-circle ml-auto mr-auto bg-img">
                    <img src="{{asset('public/img/profileImg.jpg')}}" class="img-fluid mx-auto d-block">
              </figure>

  </div>
          </div>
        <div class="col-lg-7 pl-0">
            <h6 class="headings-color">Lorem Ipsum available</h6>
            <p class=" span-color">Supplier: Keenthemes Released: 23.08.17</p>
          </div>
          <div class="col-lg-2">
            <h6 class="mb-0 headings-color">19,200</h6>
            <span class="span-color">Sales</span>
          </div>

        </div>


        <div class="row dot-dash align-items-center pt-3">
         <div class="col-lg-3">
             <div class="d-flex align-items-center justify-content-center">
              <figure class="d-flex align-items-center justify-content-center rounded-circle ml-auto mr-auto bg-img">
                    <img src="{{asset('public/img/profileImg.jpg')}}" class="img-fluid mx-auto d-block">
              </figure>

  </div>
          </div>
          <div class="col-lg-7 pl-0">
            <h6 class="headings-color">Lorem Ipsum available</h6>
            <p class=" span-color">Supplier: Keenthemes Released: 23.08.17</p>
          </div>
          <div class="col-lg-2">
            <h6 class="mb-0 headings-color">19,200</h6>
            <span class="span-color">Sales</span>
          </div>

        </div>

        <div class="row align-items-center pt-3">
          <div class="col-lg-3">
             <div class="d-flex align-items-center justify-content-center">
              <figure class="d-flex align-items-center justify-content-center rounded-circle ml-auto mr-auto bg-img">
                    <img src="{{asset('public/img/profileImg.jpg')}}" class="img-fluid mx-auto d-block">
              </figure>

  </div>
          </div>
           <div class="col-lg-7 pl-0">
            <h6 class="headings-color">Lorem Ipsum available</h6>
            <p class=" span-color">Supplier: Keenthemes Released: 23.08.17</p>
          </div>
          <div class="col-lg-2">
            <h6 class="mb-0 headings-color">19,200</h6>
            <span class="span-color">Sales</span>
          </div>

        </div>
      </div>

        </div><!-- tab-content-->
         
      </div><!--bg white end-->

    </div> <!-- col 4 (1) end -->


<!-- col 4 (2) start --><div class="col-lg-4 d-none">
    <div class="bg-white pt-4">
    <div class="row pl-4 align-items-center best-seller position-relative">
      <div class="col-lg-5">
        <h5 class="mb-0 pt-2 headings-color">Accounts</h5>
      </div>


      <div class="col-lg-7">
  <!--     <div class="card mt-3 tab-card"> -->
        <!-- <div class="card-header tab-card-header"> -->
          <ul class="nav nav-tabs card-header-tabs pull-right pr-3 headings-color" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link" id="one-tab" data-toggle="tab" href="#accountone" role="tab" aria-controls="One" aria-selected="true">Receivable</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" id="two-tab" data-toggle="tab" href="#accounttwo" role="tab" aria-controls="Two" aria-selected="false">Payable</a>
            </li>
          </ul>
        </div>
      </div>

<div class="tab-content" id="myTabContent">
  <div class="tab-pane fade show active p-3" id="accountone" role="tabpanel" aria-labelledby="one-tab">
  <div class="bg-white pt-4">
  <table class="table accounts-table headings-color">
    <tbody>
      <tr>
        <td>Lorem Ipsum</td>
        <td class="text-right">$15,023</td>
      </tr>

            <tr>
        <td>Lorem Ipsum</td>
        <td class="text-right">$15,023</td>
      </tr>
      <tr>
        <td>Lorem Ipsum</td>
        <td class="text-right">$15,023</td>
      </tr>

            <tr>
        <td>Lorem Ipsum</td>
        <td class="text-right">$15,023</td>
      </tr>

           <tr>
        <td>Lorem Ipsum</td>
        <td class="text-right">$15,023</td>
      </tr>

           <tr>
        <td>Lorem Ipsum</td>
        <td class="text-right">$15,023</td>
      </tr>

           <tr>
        <td>Lorem Ipsum</td>
        <td class="text-right">$15,023</td>
      </tr>

            <tr>
        <td >Lorem Ipsum</td>
        <td class="text-right">$15,023</td>
      </tr>
      
    </tbody>
  </table>
</div>
</div>

<div class="tab-pane fade p-3" id="accounttwo" role="tabpanel" aria-labelledby="one-tab">
  <div class="bg-white pt-4 ">
  <table class="table accounts-table">
    <tbody>
           <tr class="headings-color">
        <td>Lorem Ipsum</td>
        <td class="text-right">$15,023</td>
      </tr>

      <tr class="headings-color">
        <td>Lorem Ipsum</td>
        <td class="text-right">$15,023</td>
      </tr>

      <tr class="headings-color">
        <td>Lorem Ipsum</td>
        <td class="text-right">1$15,023</td>
      </tr>

      <tr >
        <td>Lorem Ipsum</td>
        <td class="text-right">$15,023</td>
      </tr>

      <tr class="headings-color">
        <td>Lorem Ipsum</td>
        <td class="text-right">$15,023</td>
      </tr>

      <tr class="headings-color">
        <td>Lorem Ipsum</td>
        <td class="text-right">$15,023</td>
      </tr>

      <tr class="headings-color">
        <td>Lorem Ipsum</td>
        <td class="text-right">$15,023</td>
      </tr>

      <tr class="headings-color">
        <td>Lorem Ipsum</td>
        <td class="text-right">$15,023</td>
      </tr>
      
    </tbody>
  </table>
</div>
</div>
        </div><!-- tab-content-->
         
      </div><!--bg white end-->

    </div><!-- col 4 (2 ) end -->


<!-- col 4 (3) start --><div class="col-lg-4 d-none">
    <div class="bg-white pt-4 h-100">
    <div class="d-flex justify-content-between pl-4 pr-5 sales-seller position-relative">

      <div>
        <h5 class="headings-color">Sales Stats</h5>
      </div>
      <div class="dropdown">

    <span class="dropdown-toggle fa fa-ellipsis-h" data-toggle="dropdown" ></span>
    <div class="dropdown-menu">
      <a class="dropdown-item" href="#">Link 1</a>
      <a class="dropdown-item" href="#">Link 2</a>
      <a class="dropdown-item" href="#">Link 3</a>
    </div>

      </div>

</div>

<div class="row">
  <div class="col-lg-12 pt-4">
    <div class="bg-white">
  <table class="table sales-table mb-4">
      <thead class="customer-color">
        <th class="pl-4">Customers</th>
        <th>Date</th>
        <th>Amount</th>
      </thead>
      <tbody class="headings-color">
      <tr class="sale-tr">
        <td class="pl-4">Lorem Ipsum</td>
        <td>02/28/19</td>
        <td>$14,740</td>
      </tr>

      <tr class="sale-tr">
        <td class="pl-4">Lorem Ipsum</td>
        <td>02/28/19</td>
        <td>$14,740</td>
      </tr>

      <tr class="sale-tr">
        <td class="pl-4">Lorem Ipsum</td>
        <td>02/28/19</td>
        <td>$14,740</td>
      </tr>

      <tr class="sale-tr">
        <td class="pl-4">Lorem Ipsum</td>
        <td>02/28/19</td>
        <td>$14,740</td>
      </tr>

      <tr class="sale-tr">
        <td class="pl-4">Lorem Ipsum</td>
        <td>02/28/19</td>
        <td>$14,740</td>
      </tr>

      <tr class="sale-tr">
        <td class="pl-4">Lorem Ipsum</td>
        <td>02/28/19</td>
        <td>$14,740</td>
      </tr>

      <tr class="sale-tr">
        <td class="pl-4">Lorem Ipsum</td>
        <td>02/28/19</td>
        <td>$14,740</td>
      </tr>

      <tr class="sale-tr">
        <td class="pl-4">Lorem Ipsum</td>
        <td>02/28/19</td>
        <td>$14,740</td>
      </tr>

      <tr>
        <td class="pl-4">Lorem Ipsum</td>
        <td>02/28/19</td>
        <td>$14,740</td>
      </tr>
      
    </tbody>
  </table>
</div>

        </div>
      </div>
  </div>
</div>

</div>


    </div><!-- col 4 (3 ) end -->    




</div><!-- lower section end -->
</div> <!-- main content end here -->
</div><!-- main content end here -->


<!-- <div class="row">
    <div class="col-xl-3 col-sm-6 mb-3">
        <div class="card text-white bg-primary o-hidden h-100">
            <div class="card-body">
                <div class="card-body-icon">
                    <i class="fa fa-fw fa-comments"></i>
                </div>
                <div class="mr-5">Quotations</div>
            </div>
            <a class="card-footer text-white clearfix small z-1" href="{{route('completed-quotations')}}">
                <span class="float-left">View Details</span>
                <span class="float-right"><i class="fa fa-angle-right"></i></span>
            </a>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6 mb-3">
        <div class="card text-white bg-warning o-hidden h-100">
            <div class="card-body">
                <div class="card-body-icon">
                    <i class="fa fa-fw fa-list"></i>
                </div>
                <div class="mr-5"> Draft Invoices</div>
            </div>

            <a class="card-footer text-white clearfix small z-1" href="{{route('draft-invoices')}}">
                <span class="float-left">View Details</span>
                <span class="float-right"><i class="fa fa-angle-right"></i></span>
            </a>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6 mb-3">
        <div class="card text-white bg-success o-hidden h-100">
            <div class="card-body">
                <div class="card-body-icon">
                    <i class="fa fa-fw fa-user-plus"></i>
                </div>
                <div class="mr-5">{{$totalCustomers}} Customers</div>
            </div>
            <a class="card-footer text-white clearfix small z-1" href="{{route('list-customer')}}">
                <span class="float-left">View Details</span>
                <span class="float-right"><i class="fa fa-angle-right"></i></span>
            </a>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6 mb-3">
    <div class="card text-white bg-danger o-hidden h-100">
        <div class="card-body">
            <div class="card-body-icon">
                <i class="fa fa-fw fa-shopping-cart"></i>
            </div>
            <div class="mr-5"> Products</div>
        </div>
        <a class="card-footer text-white clearfix small z-1" href="{{route('products-list')}}">
            <span class="float-left">View Details</span>
            <span class="float-right"><i class="fa fa-angle-right"></i></span>
        </a>
    </div>
</div>
</div>

</div> -->
<!--  Content End Here -->
<!-- <script type="text/javascript">
  $(document).ready(function(){
    Highcharts.chart('unit_unshipped', {
          chart: {
          type: 'column'
          },
          title: {
          text: 'Units UnShipped'
          },
          subtitle: {
          text: data.type,
          },
          xAxis: {
          type: 'category'
          },
          yAxis: {
          title: {
            text: 'Total unshipped units'
          }

          },
          legend: {
          enabled: false
          },
          plotOptions: {
          series: {
            borderWidth: 0,
            dataLabels: {
                enabled: true,
                format: '{point.y}'
            }
          }
          },

          tooltip: {
          headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
          pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y}'
          },

          series: [
            {
              name: "Total",
              colorByPoint: true,
              data: data.series_total
                  
            },
            {
              name: "Units Unshipped",
              colorByPoint: true,
              data: data.series
                  
            }
            
          ],
          // colors:data.colors,

          });
  });
</script>
 -->
@endsection

