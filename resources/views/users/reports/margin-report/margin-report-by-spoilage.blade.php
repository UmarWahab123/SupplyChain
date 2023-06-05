@extends('backend.layouts.layout')

@section('title','Product Sales Report | Supply Chain')

@section('content')
<div class="row">
  <div class="col-md-12">
    <a href="{{ url()->previous() }}" class="float-left pt-3">
    <span class="vertical-icons" title="Back">
    <img src="{{asset('public/icons/back.png')}}" width="27px">
    </span>
    </a>
    <ol class="breadcrumb" style="background-color:transparent; font-size: 20px; color: blue !important;">
        @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 3 || Auth::user()->role_id == 4 || Auth::user()->role_id == 11)
          <li class="breadcrumb-item"><a href="{{route('sales')}}">Home</a></li>
        @elseif(Auth::user()->role_id == 2)
          <li class="breadcrumb-item"><a href="{{route('purchasing-dashboard')}}">Home</a></li>
        @elseif(Auth::user()->role_id == 5)
          <li class="breadcrumb-item"><a href="{{route('importing-dashboard')}}">Home</a></li>
        @elseif(Auth::user()->role_id == 6)
          <li class="breadcrumb-item"><a href="{{route('warehouse-dashboard')}}">Home</a></li>
        @elseif(Auth::user()->role_id == 7)
          <li class="breadcrumb-item"><a href="{{route('account-recievable')}}">Home</a></li>
        @elseif(Auth::user()->role_id == 9)
          <li class="breadcrumb-item"><a href="{{route('ecom-dashboard')}}">Home</a></li>
        @elseif(Auth::user()->role_id == 10)
          <li class="breadcrumb-item"><a href="{{route('roles-list')}}">Home</a></li>
        @endif
          <li class="breadcrumb-item active">Margin Report by Spoilage</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}
<div class="row mb-3">
  <div class="col-md-8 title-col col-6">
    <h5 class="maintitle text-uppercase fontbold">Admin Management & Sales Report</h5>
  </div>
  <div class="col-md-4 col-6">
  <div class="pull-right">
    <span class="export-btn vertical-icons mr-4" title="Create New Export">
      <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
    </span>
  </div>
</div>
</div>

@include('users.reports.margin-report.dropdown-boxes')
<div class="row entriestable-row mt-3">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
      <table class="table entriestable table-bordered text-center product-sales-report">
        <thead>
          <tr>
            <th class="nowrap">PF#</th>
            <th class="nowrap">Supplier</th>
            <th class="nowrap">Customer Ref #</th>
            <th class="nowrap">Quantity</th>
            <th class="nowrap">Cogs / Unit</th>
            <th class="nowrap">Cogs Total</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>
<!--  Content End Here -->
<!-- Loader Modal -->
<div class="modal" id="loader_modal" role="dialog">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">

    <div class="modal-body">
      <h3 style="text-align:center;">Please wait</h3>
      <p style="text-align:center;"><img src="{{ asset('public/uploads/gif/waiting.gif') }}"></p>
    </div>

    </div>
  </div>
</div>

@endsection

@section('javascript')
<script type="text/javascript">
    var table2 = $('.product-sales-report').DataTable({
      "sPaginationType": "listbox",
      processing: false,
      searching:true,
    //   oLanguage:
    //   {
    //     sProcessing: '<img src="{{ asset('public/uploads/gif/waiting.gif') }}">'
    //   },
    //   "language": {
    //   processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      // ordering: true,
      serverSide: true,
      "aaSorting": [[3]],
      bSort: false,
      info: true,
      retrieve: true,
      scrollX: true,
      scrollY : '90vh',
      scrollCollapse: true,
      fixedHeader: true,
      // dom: 'Blfrtip',
      // pageLength: {{500}},
      dom: 'Bfrtip',
      // buttons: [
      //   'colvis'
      // ],
      lengthMenu: [ 100,200,400,500,1000],
      ajax: {
        beforeSend: function(){
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
        $("#loader_modal").modal('show');
        },
        url:"{!! route('get-margin-report-13') !!}",
        data: function(data) {
          },
        method: "get",
      },
      columns: [
        { data: 'refrence_code', name: 'refrence_code'},
        { data: 'default_supplier', name: 'default_supplier'},
        { data: 'customer', name: 'customer'},
        { data: 'quantity', name: 'quantity' },
        { data: 'unit_cogs', name: 'unit_cogs' },
        { data: 'cogs_total', name: 'cogs_total'},
      ],
      initComplete: function () {
        // Enable THEAD scroll bars
          $('.dataTables_scrollHead').css('overflow', 'auto');
          $('.dataTables_scrollHead').on('scroll', function () {
          $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
        });
        $('body').find('.dataTables_scrollBody').addClass("scrollbar");
        $('body').find('.dataTables_scrollHead').addClass("scrollbar");
      },
      drawCallback: function(){
        $('#loader_modal').modal('hide');
      },
    });
    
</script>
<script src="{{asset("public\site\assets\backend\js\margin-report.js")}}"></script>
@stop
