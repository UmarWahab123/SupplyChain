@extends('sales.layouts.layout')
@section('title','Stats')


@section('content')
    <!-- Right Content Start Here -->
    <div class="right-contentIn">
        <!-- upper section start -->
        <div class="row mb-3">
            <!-- left Side Start -->
            <div class="col-lg-12">
                <!-- 1st four box row start -->
                <p>SALE ORDERS with Maximum Products</p>
                <div class="row mb-3">
                    <div class="col-lg col-md-4 pb-md-3 ">
                        <div href="#" title="Quotation" class="">
                            <div class="bg-white box1 pt-4 pb-4 h-100">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="title pl-2">
                                        <h6 class="mb-0 headings-color number-size">
                                            Quotation: {{ $max_products_quotation["products"] }}
                                        </h6>
                                        <h6 class="mb-0 headings-color number-size">
                                            Order ID: {{ $max_products_quotation["order_id"] }}
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg col-md-4 pb-md-3 ">
                        <div href="#" title="Draft Order" class="">
                            <div class="bg-white box1 pt-4 pb-4 h-100">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="title pl-2">
                                        <h6 class="mb-0 headings-color number-size">
                                            Draft Order: {{ $max_products_draft["products"] }}
                                        </h6>
                                        <h6 class="mb-0 headings-color number-size">
                                            Order ID: {{ $max_products_draft["order_id"] }}
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg col-md-4 pb-md-3 ">
                        <div href="#" title="Invoice Order" class="">
                            <div class="bg-white box1 pt-4 pb-4 h-100">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="title pl-2">
                                        <h6 class="mb-0 headings-color number-size">
                                            Invoice Order: {{ $max_products_invoice["products"] }}
                                        </h6>
                                        <h6 class="mb-0 headings-color number-size">
                                            Order ID: {{ $max_products_invoice["order_id"] }}
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- first four box row end-->
            </div>
            <!-- left Side End -->
            <!-- upper section end  -->
        </div>
        <!-- upper section start -->

        <div class="row mb-3">
            <!-- left Side Start -->
            <div class="col-lg-12">
                <!-- 1st four box row start -->
                <p>PURCHASE ORDERS with Maximum Products</p>
                <div class="row mb-3">
                    @foreach($purchasingStatuses as $status)
                        <div class="col-lg col-md-4 pb-md-3 ">
                            <div href="#" title="Quotation" class="">
                                <div class="bg-white box1 pt-4 pb-4 h-100">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <div class="title pl-2">
                                            <h6 class="mb-0 headings-color number-size">
                                                {{ $status->title }}: {{ $max_products_purchasing_order[$status->id]["products"] }}
                                            </h6>
                                            <h6 class="mb-0 headings-color number-size">
                                                Order ID: {{ $max_products_purchasing_order[$status->id]["order_id"] }}
                                            </h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>
                <!-- first four box row end-->
            </div>
            <!-- left Side End -->
            <!-- upper section end  -->
        </div>
    </div>
@endsection
