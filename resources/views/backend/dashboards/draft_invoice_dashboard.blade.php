@extends('backend.layouts.layout')
@section('title','Dashboard')


@section('content')

{{-- Content Start from here --}}

<!-- Right Content Start Here -->
<div class="right-contentIn">

<!-- upper section start -->
<div class="row mb-3">

@include('backend.layouts.dashboard-boxes')
</div>


<div class="row align-items-center mb-3">
  <div class="col-md-8 title-col">
      <h4 class="maintitle">My Draft Invoices</h4>
  </div>    
</div>

<div class="row mb-3 headings-color">

{{-- <div class="col-lg-9">
  <h4>My Draft Invoices</h4>
</div> --}}

<div class="col-lg-12">
  <form id="form_id">
  <div class="row">

    <div class="col">
      <div class="form-group">
        <select class="form-control selecting-tables sort-by-value">
            <option value="2" selected>-- Draft Invoices --</option>
            <option value="7">Selecting Vendors</option>
            <option value="8">Purchasing</option>
            <option value="9">Importing</option>
            <option value="10">Delivery</option>
        </select>
      </div>
    </div>

    <div class="col">
      <div class="form-group">
        <select class="form-control selecting-customer">
            <option value="">-- Customers --</option>
            @foreach($customers as $customer)
            <option value="{{$customer->id}}">{{$customer->company}}</option>
            @endforeach
        </select>
      </div>
    </div>

    <div class="col">
      <div class="form-group">
        <input type="date" class="form-control" name="from_date" id="from_date">
    </div>
  </div>

    <div class="col">
      <div class="form-group">
        <input type="date" class="form-control" name="to_date" id="to_date">
    </div>
  </div>

    <div class="col">
    <div class="input-group-append ml-3">
              <span class="reset common-icons" title="Reset">
          <img src="{{asset('public/icons/reset.png')}}" width="27px">
        </span>  
    </div>
  </div>

  </div>
  </form>
</div>    


<div class="row entriestable-row col-lg-12 pr-0 quotation" id="quotation">
  <div class="col-12 pr-0">
    <div class="entriesbg bg-white custompadding customborder">
      <table class="table entriestable table-bordered table-quotation text-center">
        <thead>
          <tr>
            <th>Action</th>
            <th>Order#</th>
            <th>Company</th>
            <th>Customer #</th>
            <th>Date Purchase</th>
            <th>Order Total</th>
            <th>@if(!array_key_exists('target_ship_date', $global_terminologies)) Target Ship Date @else {{$global_terminologies['target_ship_date']}} @endif</th>
            <th>Status</th>
          </tr>
        </thead>               
      </table>
    </div>  
  </div>
</div>
</div>

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

<!-- main content end here -->
</div>
@endsection


@section('javascript')
<script type="text/javascript">
  $(function(e){
    $('.sort-by-value').on('change', function(e){      
        $('.table-quotation').DataTable().ajax.reload();
        document.getElementById('quotation').style.display = "block";
    });
    
    $('.selecting-customer').on('change', function(e){
      $('.table-quotation').DataTable().ajax.reload();
      document.getElementById('quotation').style.display = "block";
    });

    $('#from_date').change(function() {
      var date = $('#from_date').val();
      $('.table-quotation').DataTable().ajax.reload();
      document.getElementById('quotation').style.display = "block";
    });

    $('#to_date').change(function() {
      var date = $('#to_date').val();
      $('.table-quotation').DataTable().ajax.reload();
      document.getElementById('quotation').style.display = "block";
    });

    $('.reset').on('click',function(){
      $('#form_id').trigger("reset");
      $('.sort-by-value').val(2).change();
    });
    
    $('.table-quotation').DataTable({
         processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        ordering: false,
        serverSide: false,
        dom: 'ftipr',
         "columnDefs": [
    { className: "dt-body-left", "targets": [ 1,2,3,4,6,7 ] },
    { className: "dt-body-right", "targets": [5] }
  ],
        scrollX: true,
        ajax:{
          url:"{!! route('get-completed-quotation-admin') !!}",
          data: function(data) { data.dosortby = $('.sort-by-value option:selected').val(),data.selecting_customer = $('.selecting-customer option:selected').val(),data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val() } ,
        },
        columns: [
            { data: 'action', name: 'action' },
            { data: 'ref_id', name: 'ref_id' },
            { data: 'customer', name: 'customer' },
            { data: 'customer_ref_no', name: 'customer_ref_no' },
            { data: 'invoice_date', name: 'invoice_date' },
            { data: 'total_amount', name: 'total_amount' },
            { data: 'target_ship_date', name: 'target_ship_date' },
            { data: 'status', name: 'status' },
        ]
   });

  @if(Session::has('successmsg'))
      toastr.success('Success!', "{{ Session::get('successmsg') }}",{"positionClass": "toast-bottom-right"});  
      @php 
       Session()->forget('successmsg');     
      @endphp  
  @endif
  });
</script>
@stop