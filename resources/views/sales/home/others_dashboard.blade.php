@extends('sales.layouts.layout')
@section('title','Dashboard')
@section('content')
{{-- <style type="text/css">
  .dt-buttons{
    float: right !important;
      
  }

  .dt-buttons button{
      background: #13436c;
    color: #fff;
    border-radius: 0px;
    font-size: 11px;
    max-height: 34px;
  }
  .dt-buttons button:hover:not(.disabled){
     background-color: #13436c !important;
    color: #fff;
    background-image: none !important;
  }


</style> --}}
{{-- Content Start from here --}}

<!-- Right Content Start Here -->
<div class="right-contentIn">

<!-- upper section start -->
<div class="row mb-3">
<!-- left Side Start -->

<div class="col-lg-12">
  <!-- 1st four box row start -->
  <div class="row mb-3">
    @if(Auth::user()->role_id == 7)
    @include('accounting.layouts.dashboard-boxes')
    @else
    @include('sales.layouts.dashboard-boxes')
    @endif
  </div>
  <!-- first four box row end-->
</div>
<!-- left Side End -->
<!-- upper section end  -->
</div>

<div class="row mb-3 headings-color">

<div class="col-lg-9">
  @if(Session::has('status'))
  @php $status = Session::get('status') @endphp
  @if(@$status == 11)
  <h4>My UnPaid Other</h4>
  @else
  <h4>My Paid Other</h4>
  @endif
  @else
  <h4>Other</h4>
  @endif
</div>


<div class="col-lg-12 col-md-12">
  <form id="form_id">
  <div class="row">
    <div class="col-lg col-md-6" id="invoice-1">
      <label><b>Other</b></label>
      <div class="form-group">
        <select class="form-control selecting-tables state-tags sort-by-value">
            <option value="31" selected>-- Other --</option>
             @foreach(@$quotation_statuses as $status)
           <option value="{{@$status->id}}">{{@$status->title}}</option>
           @endforeach
        </select>
      </div>
    </div>

    <!-- <div class="col-lg col-md-6">
      <div class="form-group">
        <select class="form-control selecting-customer">
            <option value="">-- Customers --</option>
            @foreach($customers as $customer)
            <option value="{{$customer->id}}">{{$customer->reference_name}}</option>
            @endforeach
        </select>
      </div>
    </div> -->
<div class="col-lg col-md-6" id="Choose-customer-2">
  <label><b>Customer</b></label>
      <div class="form-group">
        <select class="font-weight-bold form-control-lg form-control js-states state-tags selecting-customer" name="customer" required="true">
            <option value="">Choose Customer</option>
            @foreach($customers as $customer)
            @php $id = Session::get('customer_id'); @endphp
            <option value="{{$customer->id}}" {{ ($customer->id == @$id )? "selected='true'":" " }}>{{@$customer->reference_name}}</option>
            @endforeach
        </select>
      </div>
    </div>

    <div class="col-lg col-md-6" id="customer-group-2">
      <label><b>Customer Group</b></label>
      <div class="form-group">
        <select class="font-weight-bold form-control-lg form-control js-states state-tags selecting-customer-group" name="customer" required="true">
            <option value="">@if(!array_key_exists('customer_group', $global_terminologies))Customer Group @else {{$global_terminologies['customer_group']}} @endif</option>
            @foreach($customer_categories as $cat)
            <option value="{{$cat->id}}">{{@$cat->title}}</option>
            @endforeach
        </select>
      </div>
    </div>
    <div class="col-lg col-md-4" id="sale-person-2">
      <label><b>Sale Person</b></label>
      <div class="form-group">
        <select class="form-control state-tags selecting-sale">
            <option value="">-- Sale person --</option>
           @foreach($sales_persons as $person)
            <option value="{{$person->id}}">{{$person->name}}</option>
            @endforeach
        </select>
      </div>
    </div>

    <div class="col-lg col-md-6">
      <label><b>Delivery Date</b></label>
      <div class="form-group">
        <input type="text" placeholder="From Date" name="from_date" class="form-control font-weight-bold" id="from_date" autocomplete="off">
      </div>
    </div>

    <div class="col-lg col-md-6">
      <label><b>Delivery Date</b></label>
      <div class="form-group">
        <input type="text" placeholder="To Date" name="to_date" class="form-control font-weight-bold" id="to_date" autocomplete="off">
      </div>
    </div>

    <div class="col-lg col-md-6">
      <label><b>Orders# Search</b></label>
      <div class="form-group">
        <input type="text" placeholder="Search Order# & Draft#" name="input_keyword" class="form-control font-weight-bold" id="input_keyword" autocomplete="off">
      </div>
    </div>

    

  <div class="col-lg col-md-4 ml-md-auto mr-md-auto pb-md-3" id="reset-2">
      <label><b style="visibility: hidden;">Reset</b></label>
    <div class="input-group-append ml-3">
              <span class="reset common-icons" title="Reset">
          <img src="{{asset('public/icons/reset.png')}}" width="27px">
        </span>  
    </div>
  </div>
  </div>
  </form>
  
  <div class="col-lg-2 col-md-4 ml-md-auto mr-md-auto pb-md-3 pull-right" id="reset-2">
      <label><b style="visibility: hidden;">Export</b></label>
    <div class="input-group-append ml-3">
          <button id="export_s_p_r" class="btn recived-button " >Export</button>    
    </div>
  </div>


</div>    


<div class="row entriestable-row col-lg-12 pr-0 quotation" id="quotation">
  <div class="alert alert-danger d-none not-cancelled-alert col-lg-12 ml-3">
  
  </div>
  @if(Auth::user()->role_id != 7)
  <div class="selected-item catalogue-btn-group mt-4 mt-sm-3 ml-3 d-none">
    
      <a href="javascript:void(0);" class="btn selected-item-btn btn-sm cancel-quotations
      deleteIcon" title="Cancel"><span><i class="fa fa-times" ></i></span></a>

  </div>
  @endif
  <div class="col-12 pr-0">
    <div class="entriesbg bg-white custompadding customborder">
          <table class="table entriestable table-bordered table-quotation text-center">
              <thead>
                  <tr>
                      <th>
                        <div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                          <input type="checkbox" class="custom-control-input check-all1" name="check_all" id="check-all">
                          <label class="custom-control-label" for="check-all"></label>
                        </div>
                      </th>
                      <!-- <th>Action</th> -->
                      <th>Order#</th>
                      <th>Sales Person</th>
                      <th>Customer #</th>
                      <th>@if(!array_key_exists('reference_name', $global_terminologies)) Reference Name  @else {{$global_terminologies['reference_name']}} @endif</th>
                      <th>Company Name</th>
                      <th>Draft#</th>
                      <th>Inv.#</th>
                      <th>VAT Inv (-1)</th>
                      <th>VAT</th>
                      <th>Inv.#</th>
                      <th>Non VAT <br>Inv (-2)</th>
                      <!-- <th>Date Purchase</th> -->
                      <th>Order Total</th>
                      <th>Delivery Date</th>
                      <!-- <th>Target Ship Date</th> -->
                      <th>Comment To <br> Warehouse</th>
                      <th>Ref. Po #</th>
                      <th>Status</th>
                  </tr>
              </thead>
                <tfoot>
            <tr>
                <th colspan="5" style="text-align: right;"></th>
                
                <th colspan="4" style="text-align: left;"></th>
                
            </tr>
        </tfoot>
          </table>
          <input type="hidden" name="is_paid" class="is_paid" value="0">
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

<form id="export_sold_product_report_form" method="post"  action="{{route('export-invoice-table') }}">
  @csrf
  <input type="hidden" name="dosortbyx" id="dosortbyx">
  <input type="hidden" name="selecting_customerx" id="selecting_customerx">
  <input type="hidden" name="from_datex" id="from_datex">
  <input type="hidden" name="to_datex" id="to_datex">
  <input type="hidden" name="selecting_salex" id="selecting_salex">
  <input type="hidden" name="typex" id="typex" value="invoice">
  <input type="hidden" name="selecting_customer_groupx" id="selecting_customer_groupx">
  <input type="hidden" name="is_paidx" id="is_paidx" value="0">

</form>

@endsection


@section('javascript')
<script type="text/javascript">

  // search of a invoice code start here
  $('#input_keyword').keyup(function(event){
    var query = $(this).val();
    if(event.keyCode == 13)
    {
      if(query.length > 2)
      {
        // alert(query);
        $('.table-quotation').DataTable().ajax.reload();
      }
      else if(query.length == 0)
      {
        $('.table-quotation').DataTable().ajax.reload(); 
      }
      else
      {
        // $('#input_keyword').empty();
        toastr.error('Error!', 'Please enter atlesat 3 characters then press Enter !!!' ,{"positionClass": "toast-bottom-right"});
      }
    }
  });
  // search of a invoice code ends here

  $("#from_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

  $("#to_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });


  $(document).ready(function(){
  
  var last_month = new Date();
  last_month.setDate( last_month.getDate() - 30 );
  // let today = new Date().toISOString().substr(0, 10);
  // document.querySelector("#to_date").value = today;
  // document.querySelector("#from_date").value = last_month.toISOString().substr(0, 10);

  var currentTime = new Date();
  // First Date Of the month 
  var startDateFrom = new Date(currentTime.getFullYear(),currentTime.getMonth(),1);

  $('#from_date').datepicker('setDate', last_month);
  // $('#to_date').datepicker('setDate', 'today');

$('#from_datex').val($('#from_date').val());
@if(Session::has('total_invoices'))
// // alert('yes');
// var last_month = new Date();
//   var first_date = new Date(last_month.getFullYear(), last_month.getMonth(), 1);
//   first_date.setDate( first_date.getDate() + 1 );
//   // alert(first_date.toISOString().substr(0, 10));
//   let today1 = new Date().toISOString().substr(0, 10);
//   document.querySelector("#from_date").value = first_date.toISOString().substr(0, 10);
//   document.querySelector("#to_date").value = today1;
  
  var currentTime = new Date();
  // First Date Of the month 
  var startDateFrom = new Date(currentTime.getFullYear(),currentTime.getMonth(),1);

  $('#from_date').datepicker('setDate', startDateFrom);
  // $('#to_date').datepicker('setDate', 'today');

@elseif(Session::has('find'))
var id = "{{Session::get('customer_id')}}";
var dat = "{{Session::get('month')}}";

var full_date = dat.split('-');
var year = full_date[0];
var month = full_date[1];
var datee = '01';

var year1 = full_date[0];
var month1 = full_date[1];


var getDaysInMonth = function(month,year) {
  // Here January is 1 based
  //Day 0 is the last day in the previous month
 return new Date(year, month, 0).getDate();
// Here January is 0 based
// return new Date(year, month+1, 0).getDate();
};

var datee1 = getDaysInMonth(month1, year1);

var from_date =  datee+ "/" + month + "/" + year;
var to_date =  datee1+ "/" + month1 + "/" + year1;
// alert(dateStr);
document.querySelector("#from_date").value = from_date;
document.querySelector("#to_date").value = to_date;


@endif
@if(Session::has('status')){
  var status = "{{Session::get('status')}}";
  if(status == 11){
    $('.is_paid').val(11);
        $('#is_paidx').val(11);

    // alert($('.is_paid').val());
    // return;
     // $('.table-quotation').DataTable().ajax.reload();
  }
   if(status == 24){
    $('.is_paid').val(24);
        $('#is_paidx').val(24);

    // alert($('.is_paid').val());
    // return;
     // $('.table-quotation').DataTable().ajax.reload();
  }
}
@endif
@if(Session::has('year'))
  var year = "{{Session::get('year')}}";
  var from_date = year+'-01-01';
  var to_date = year+'-12-31';
  console.log(from_date,to_date);
  document.querySelector("#from_date").value = from_date;
  document.querySelector("#to_date").value = to_date;

@endif
});
  $(function(e){
      
  
    $(".state-tags").select2({dropdownCssClass : 'bigdrop'});
    
    $("#dosortbyx").val($('.sort-by-value option:selected').val());

    $('.sort-by-value').on('change', function(e){        
      $("#dosortbyx").val($('.sort-by-value option:selected').val());
      $('.table-quotation').DataTable().ajax.reload();
      document.getElementById('quotation').style.display = "block";
    });
    
    $('.selecting-customer').on('change', function(e){
      $("#selecting_customerx").val($('.selecting-customer option:selected').val());

      $('.table-quotation').DataTable().ajax.reload();
      document.getElementById('quotation').style.display = "block";
    });

    
    $('.selecting-customer-group').on('change', function(e){
      $("#selecting_customer_groupx").val($('.selecting-customer-group option:selected').val());

      $("#selecting_customer_groupx").val($('.selecting-customer-group option:selected').val());

      // alert($('.selecting-customer option:selected').val());
      
      $('.table-quotation').DataTable().ajax.reload();
      $($.fn.dataTable.tables(true)).DataTable()
        .columns.adjust();
      document.getElementById('quotation').style.display = "block";

    });

    $('#from_date').change(function() {
      var date = $('#from_date').val();
      $("#from_datex").val(date);

      $('.table-quotation').DataTable().ajax.reload();
      document.getElementById('quotation').style.display = "block";
    });

    $('#to_date').change(function() {
      var date = $('#to_date').val();
      $("#to_datex").val(date);

      $('.table-quotation').DataTable().ajax.reload();
      document.getElementById('quotation').style.display = "block";
    });

    $('.reset').on('click',function(){
      // $('.selecting-customer').val('');
      // $('.selecting-customer-group').val('');
      // $('.sort-by-value').val(3);
      $('#input_keyword').empty();
      $('#form_id').trigger("reset");

      $('#dosortbyx').val(3);
      $('#selecting_customerx').val(null);
      $('#from_datex').val(null);
      $('#to_datex').val(null);
      $('#selecting_salex').val(null);
      $('#selecting_customer_groupx').val(null);

      $(".state-tags").select2("destroy");
      $(".state-tags").select2(); 
      $('.table-quotation').DataTable().ajax.reload();
      // $('.table-quotation').DataTable().fnDraw();
    });

    $('#export_s_p_r').on('click',function(){
      $("#export_sold_product_report_form").submit(); 


    });
    
    var table2 = $('.table-quotation').DataTable({
      processing: true,
      searching:false,
      "language": {
      processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      ordering: false,
      serverSide: true,
      "lengthMenu": [100,200,300,400],
        // dom: 'ftipr',
        // dom: 'Bfrtip',
      "columnDefs": [
      { className: "dt-body-left", "targets": [ 1,2,3,4,5,6,9,12,13,14 ] },
      { className: "dt-body-right", "targets": [7,8,10,11] }
    ],
        scrollX: true,
        scrollY : '90vh',
        scrollCollapse: true,
        ajax:{
          beforeSend: function(){
            $('#loader_modal').modal('show');
          },
          url:"{!! route('get-completed-other') !!}",
          data: function(data) { data.dosortby = $('.sort-by-value option:selected').val(),data.selecting_customer = $('.selecting-customer option:selected').val(),data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val() ,data.selecting_sale = $('.selecting-sale option:selected').val(),data.type = 'invoice',data.selecting_customer_group = $('.selecting-customer-group option:selected').val(),data.is_paid = $('.is_paid').val() ,data.input_keyword = $('#input_keyword').val()} ,
        },
        columns: [
            { data: 'checkbox', name: 'checkbox' },
            // { data: 'action', name: 'action' },
            { data: 'inv_no', name: 'inv_no' },
            { data: 'sales_person', name: 'sales_person' },
            { data: 'customer_ref_no', name: 'customer_ref_no' },
            { data: 'customer', name: 'customer' },
            { data: 'customer_company', name: 'customer_company' },
            { data: 'ref_id', name: 'ref_id' },
            { data: 'reference_id_vat', name: 'reference_id_vat' },
            { data: 'sub_total_1', name: 'sub_total_1' },
            { data: 'vat_1', name: 'vat_1' },
             { data: 'reference_id_vat_2', name: 'reference_id_vat_2' },
            { data: 'sub_total_2', name: 'sub_total_2' },
            // { data: 'invoice_date', name: 'invoice_date' },
            { data: 'total_amount', name: 'total_amount' },
            { data: 'delivery_date', name: 'delivery_date' },
            // { data: 'target_ship_date', name: 'target_ship_date' },
            { data: 'comment_to_warehouse', name: 'comment_to_warehouse' },
            { data: 'memo', name: 'memo' },
            { data: 'status', name: 'status' },
        ],
          dom: 'Blfrtip',
       buttons: [
           
            {
                // extend: 'excelHtml5',
                // text: '<i class="fa fa-file-excel-o" style="font-size:22px;" title="Export Excel"></i>',
                // exportOptions: { orthogonal: 'export',columns: [2,3,4,5,6,7,8,9,10,11,12,13,14] },
                title: null,
                footer: true,
            },
        ],
         initComplete: function () {
          // Enable THEAD scroll bars
          $('.dataTables_scrollHead').css('overflow', 'auto');

          // Sync THEAD scrolling with TBODY
          $('.dataTables_scrollHead').on('scroll', function () {
              $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
          });
        },
        // drawCallback: function(){
        // },     
        "fnDrawCallback": function() {
          $('#loader_modal').modal('hide');
          // var value = $('.dataTables_filter input').val();
          // if(value != ''){
          //   var sum = 0;
          //   $('.total_id').each(function(){
          //     var total = $(this).text();
          //     var new_tot = total.replace(/[, ]+/g,'').trim();
          //       sum += parseFloat(new_tot);  // Or this.innerHTML, this.innerText
          //     // alert(new_tot);
          //   });
          //   // alert(sum);
          //   var api = this.api();
          //   $( api.column( 0 ).footer() ).html('Order Total For All Entries');
          //   $( api.column( 7 ).footer() ).html(sum);
          // }
          // else{
        var api = this.api();
        var json = api.ajax.json();
        var total = json.post;
        //alert(total);
         // total = total.toFixed(2);
         total = parseFloat(total).toFixed(2);
          total = total.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
          $( api.column( 0 ).footer() ).html('Order Total For All Entries');
            $( api.column( 7 ).footer() ).html(total);
        //alert(total);
          // }
    }
        
   });

    $('.dataTables_filter input').unbind();
$('.dataTables_filter input').bind('keyup', function(e) {
if(e.keyCode == 13) {
  // alert();
        table2.search($(this).val()).draw();
}
});

    $('.selecting-sale').on('change', function(e){

    $("#selecting_salex").val($('.selecting-sale option:selected').val());

      $('.table-quotation').DataTable().ajax.reload();
      
    });

    $(document).on('click', '.check-all1', function () {
        if(this.checked == true){
        $('.check1').prop('checked', true);
        $('.check1').parents('tr').addClass('selected');
        var cb_length = $( ".check1:checked" ).length;
        if(cb_length > 0){
          $('.selected-item').removeClass('d-none');
        }
      }else{
        $('.check1').prop('checked', false);
        $('.check1').parents('tr').removeClass('selected');
        $('.selected-item').addClass('d-none');
        
      }
    });

   $(document).on('click', '.check1', function () {
    // $(this).removeClass('d-none');
   $('.cancel-quotations').removeClass('d-none');
   $('.revert-quotations').removeClass('d-none');
        var cb_length = $( ".check1:checked" ).length;
        var st_pieces = $(this).parents('tr').attr('data-pieces');
        if(this.checked == true){
        $('.selected-item').removeClass('d-none');
        $(this).parents('tr').addClass('selected');
      }else{
        $(this).parents('tr').removeClass('selected');
        if(cb_length == 0){
         $('.selected-item').addClass('d-none');
        }
        
      }
    });


   $(document).on('click', '.cancel-quotations', function(){
    var selected_quots = [];
    $("input.check1:checked").each(function() {
      selected_quots.push($(this).val());
    });

    swal({
      title: "Alert!",
      text: "Are you sure you want to cancel selected orders?",
      type: "info",
      showCancelButton: true,
      confirmButtonClass: "btn-danger",
      confirmButtonText: "Yes!",
      cancelButtonText: "No!",
      closeOnConfirm: true,
      closeOnCancel: false
      },
      function(isConfirm) {
      if (isConfirm){
        $.ajax({

          method:"get",
          dataType:"json",
          data: {quotations : selected_quots},
          url:"{{ route('cancel-invoice-orders') }}",
          success:function(result){
            if(result.success == true)
            {
              toastr.success('Success!', 'Orders Cancelled Successfully',{"positionClass": "toast-bottom-right"});
              $('.table-quotation').DataTable().ajax.reload();
              $('.cancel-quotations').addClass('d-none');
               $('.revert-quotations').addClass('d-none');
              $('.check-all1').prop('checked',false);
             window.location.href = "{{ url('sales/get-cancelled-orders')}}";

            }
          }
        });
      }
      else{
          swal("Cancelled", "", "error");
      }
      });
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