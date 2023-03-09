@extends('importing.layouts.layout')
@section('title','Products Recieving')
<?php
use Carbon\Carbon;
?>

@section('content')
{{-- Content Start from here --}}
<div class="right-content pt-0">
  <div class="row headings-color mb-3">
   <input type="hidden" name="id" id="po_group_id" value="{{$po_group->id}}">
   <input type="hidden" name="po_group_total_gross_weight" id="po_group_total_gross_weight" value="{{$po_group->po_group_total_gross_weight}}">
   <input type="hidden" name="po_group_import_tax_book" id="po_group_import_tax_book" value="{{$po_group->po_group_import_tax_book}}">
    <div class="col-lg-4 col-md-6 d-flex align-items-center">
      <h4>Group No {{$po_group->ref_id}}<br>Product Receiving Records</h4>
    </div>  
    <div class="col-lg-5 col-md-1"></div>

    <div class="col-lg-3 col-md-5 d-flex align-items-center bg-white p-2">
      <table class="table table-bordered mb-0">
        <tbody>
          <tr>
            <th>AWB:B/L</th>
            <td>{{$po_group->bill_of_landing_or_airway_bill != null ? $po_group->bill_of_landing_or_airway_bill : 'N.A'}}</td>
          </tr>
          <tr>
            <th>Courier</th>
            <td>{{$po_group->po_courier != null ? $po_group->po_courier->title :" N.A"}}</td>
          </tr>
          <tr>
            <th>@if(!array_key_exists('target_ship_date', $global_terminologies)) Target Ship Date @else {{$global_terminologies['target_ship_date']}} @endif</th>
            <td>{{$po_group->target_receive_date != null ? date('d-m-Y', strtotime($po_group->target_receive_date)) :" N.A"}}</td>
          </tr>
          <tr>
            <th>Note</th>
            <td>N.A</td>
          </tr>
        </tbody>
        </table>
    </div>
    <div class=" col-lg-3 col-md-3 input-group mb-3 d-none">
      <input type="text" class="form-control" placeholder="ID/CustomerName">
      <div class="input-group-append ml-3">
        <button class="btn recived-button" type="submit">Search</button>  
       </div>
    </div>
  </div>

  <div class="row headings-color">
    <div class="col-lg-4 col-md-4 d-flex align-items-center fontbold mb-3">
      <a href="{{ route('incompleted-po-groups') }}">
        <button type="button" class="btn-color btn text-uppercase purch-btn mr-3 headings-color">back</button>
      </a>  
      <a href="javascript:void(0);" class="d-none">
        <button type="button" class="btn-color btn text-uppercase purch-btn headings-color export-pdf">export</button>
      </a>
      @if(@$group_detail < 11)
      <a href="javascript:void(0);" class="ml-1">
        <button type="button" class="btn-color btn text-uppercase purch-btn headings-color export-pdf2">print</button>
        @endif
      </a>
    </div>
  </div>
  <!-- export pdf form starts -->
  <form class="export-group-form" method="post" action="{{url('importing/export-group-to-pdf')}}">
    @csrf
    <input type="hidden" name="po_group_id" value="{{$po_group->id}}">
  </form>

  <!-- export pdf2 form starts -->
  <form class="export-group-form2" method="post" action="{{url('importing/export-group-to-pdf2')}}">
    @csrf
    <input type="hidden" name="po_group_id" value="{{$po_group->id}}">
  </form>

<div class="right-content pt-0">
    <div class="row mb-3 headings-color">
      <div class="col-lg-12 col-md-12 p-0">
        <div class="bg-white table-responsive p-3">
          <table class="table headings-color entriestable text-center table-bordered product_table" id="receive-table" style="width:100%">
            <thead class="sales-coordinator-thead ">
              <tr>
               <th>Po No.</th>
               <th>{{$global_terminologies["suppliers_product_reference_no"]}}</th>
               <th>Supplier</th>
               <th>{{$global_terminologies["our_reference_number"]}}</th>
               <th>{{$global_terminologies['product_description']}}</th>
               <th> Buying <br> Unit</th>
               <th> Buying <br> Currency</th>
               <th>{{$global_terminologies['qty']}} <br>Ordered</th>
               <th>{{$global_terminologies['qty']}} <br>Inv</th>
               <th>Total <br>Gross <br> Weight</th>
               <th>Total <br> Extra <br>Cost</th>
               <!-- <th>QTY Rcvd</th> -->
               <th>{{$global_terminologies['purchasing_price']}}</th>
               <th>Currency <br>Conversion <br> Rate</th>
               <th>{{$global_terminologies['purchasing_price']}} <br> in THB</th>
               <th>Total</th>
               <th>Import <br>Tax <br>(Book)%</th>
               <th>{{$global_terminologies["freight_per_billed_unit"]}}</th>
               <th>{{$global_terminologies["landing_per_billed_unit"]}}</th>
               <th>Book % Tax</th>
               <th>Weighted %</th>
               <th>Actual<br>Tax</th>
               <th>Actual<br> Tax %</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-lg-9 col-md-6"></div>
      <div class="col-lg-3 col-md-6 input-group mb-3 ">
        <table class="table table-bordered bg-white">
          <tbody>
            <tr>
              <th>Actual Tax</th>
              <td><input type="text" name="tax" placeholder="Actual Tax" class="form-control mr-1 po_group_data" data-fieldvalue="{{$po_group->tax}}" value="{{$po_group->tax}}"></td>
            </tr>
            <tr>
              <th>Freight Cost</th>
              <td><input type="text" name="freight" placeholder="Freight Cost" class="form-control mr-1 po_group_data" data-fieldvalue="{{$po_group->freight}}" value="{{$po_group->freight}}"></td>
            </tr>
            <tr>
              <th>Landing Cost</th>
              <td><input type="text" name="landing" placeholder="Landing Cost" class="form-control mr-1 po_group_data" data-fieldvalue="{{$po_group->landing}}" value="{{$po_group->landing}}"></td>
            </tr>
          </tbody>
        </table>
    </div>
    </div>
    <div class="row mb-3">
      <div class="col-lg-9 col-md-9"></div>
      <div class="col-lg-3 col-md-3">
        <a href="javascript:void(0);">
          <button type="button" data-id={{$id}} class="btn-color btn float-right confirm-to-stock-btn"><i class="fa fa-check"></i> Confirm Cost</button>
        </a>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-lg-6 col-md-9 p-0">
        <div class="p-0 h-100 bg-white">
          <table class="my-tablee table-bordered text-center">
           <thead class="sales-coordinator-thead ">
             <tr>                
              <th>User  </th>
              <th>Date/time </th>
              <th>Product </th>
              <th>Column </th>
              <th>Old Value</th>
              <th>New Value</th>
             </tr>
           </thead>
           <tbody>
            @if($product_receiving_history->count() > 0)
            @foreach($product_receiving_history as $history)
            <tr style="border:1px solid #eee;">
              <td>{{$history->get_user->name}}</td>
              <td>{{Carbon::parse(@$history->created_at)->format('d-M-Y, H:i:s')}}</td>                 
              <td>{{$history->get_pod->product->short_desc}}</td>
              <td>{{$history->term_key}}</td>
              <td>{{$history->old_value == null ? 0 : $history->old_value}}</td>                 
              <td>{{$history->new_value}}</td>                 
            </tr>                 
            @endforeach  
            @else
            <tr style="border:1px solid #eee;">
              <td colspan="6"><center>No Data Available in Table</center></td>
            </tr> 
            @endif
           </tbody>
          </table>
        </div>
      </div>
    </div>
</div>
</div>

{{-- Greater Quantity Modal --}}
<div class="modal" id="greater_quantity">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Purchase Order's </h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">
          <div class="fetched-po">
            <div class="adv_loading_spinner3 d-flex justify-content-center">
                <img class="img-spinner" src="{{ url('public/uploads/gif/waiting.gif') }}" style="margin-top: 10px;">
            </div>
          </div>
        </div>

      <div class="modal-footer">
       <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>
@endsection
 @php
      $hidden_by_default = '';
 @endphp

@section('javascript')
<script type="text/javascript">
$(function(e){  
 $(document).on('click','.condition',function(){
      var value = $(this).val();
      var id = $(this).data('id');

      savePoData(value,id);
  });

  function savePoData(value,id){
    
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method: "post",
       
        url: "{{ url('importing/edit-po-goods') }}",
        dataType: 'json',
        // data: {field_name:field_name,field_value:field_value,cust_detail_id:cust_detail_id,new_select_value:new_select_value},
        
        data: 'value='+value+'&'+'id'+'='+id,
        beforeSend: function(){
          // shahsky here
        },
        success: function(data)
        {
          if(data.success == true)
          {
            toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
            $("#receive-table").DataTable().ajax.reload(null, false );
            return true;
          }
        },
        error: function (request, status, error) {
          swal("Something Went Wrong! Contact System Administrator!");
            $('.form-control').removeClass('is-invalid');
            $('.form-control').next().remove();
            json = $.parseJSON(request.responseText);
            $.each(json.errors, function(key, value){
                $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                 $('input[name="'+key+'"]').addClass('is-invalid');
            });
        }

      });
    }
  var id = $('#po_group_id').val();
    
  var table2 = $('.product_table').DataTable({
    scrollX: true,
    scrollY : '90vh',
    scrollCollapse: true,
     processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    ordering: false,
    serverSide: true,
    fixedHeader: true,
    dom: 'Blfrtip',
    pageLength: {{100}},
    lengthMenu: [ 100, 200, 300, 400],
    colReorder: {
      realtime: false
    },
    columnDefs: [
            { targets: [{{$hidden_by_default}}], visible: false },
             { className: "dt-body-left", "targets": [ 0,1,2,3,4,5,6,7,8 ] },
    { className: "dt-body-right", "targets": [9,10,11,12,13,14,15,16,17,18,19,20] }
        ],
    buttons: [
            {
                extend: 'colvis',
                columns: ':not(.noVis)',
            }
        ],
    
    ajax:"{{ url('importing/get-details-of-po')}}"+"/"+id,
    columns: [
        { data: 'po_number', name: 'po_number' },
        { data: 'reference_number', name: 'reference_number' },
        { data: 'supplier', name: 'supplier' },
        { data: 'prod_reference_number', name: 'prod_reference_number' },
        { data: 'desc', name: 'desc' },
        { data: 'unit', name: 'unit' },
        { data: 'buying_currency', name: 'buying_currency' },
        { data: 'qty_ordered', name: 'qty_ordered' },
        { data: 'qty', name: 'qty' },
        { data: 'pod_total_gross_weight', name: 'pod_total_gross_weight' },
        { data: 'pod_total_extra_cost', name: 'pod_total_extra_cost' },
        /*{ data: 'qty_receive', name: 'qty_receive' },*/
        { data: 'buying_price', name: 'buying_price' },
        { data: 'currency_conversion_rate', name: 'currency_conversion_rate' },
        { data: 'buying_price_in_thb', name: 'buying_price_in_thb' },
        { data: 'total_buying_price', name: 'total_buying_price' },
        { data: 'import_tax_book', name: 'import_tax_book' },
        { data: 'freight', name: 'freight' },
        { data: 'landing', name: 'landing' },
        { data: 'book_tax', name: 'book_tax' },
        { data: 'weighted', name: 'weighted' },
        { data: 'actual_tax', name: 'actual_tax' },
        { data: 'actual_tax_percent', name: 'actual_tax_percent' },
    ],
      initComplete: function () {
        // Enable THEAD scroll bars
          $('.dataTables_scrollHead').css('overflow', 'auto');
          $('.dataTables_scrollHead').on('scroll', function () {
          $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
        }); 
      },
    "drawCallback": function ( settings ) {
        var api = this.api();
        var rows = api.rows( {page:'current'} ).nodes();
        var last=null;
        var colonne = api.row(0).data().length;
        var totale = new Array();
        totale['Totale']= new Array();
        var groupid = 0;
        var subtotale = new Array();

        var length = rows.length;    
        api.column(0, {page:'current'} ).data().each( function ( group, i ) {     
            if ( last !== group ) {
                //groupid++;
                last = group;
              }
              if(i == (length-1)){
                $(rows).eq( i ).after(
                  '<tr class="group"><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>'+/*<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>*/+'</tr>'
                );                   
            }               
                            
            val = api.row(api.row($(rows).eq( i )).index()).data();      //current order index
            $.each(val,function(index2,val2){
              //console.log(val2);
              if (typeof subtotale[groupid] =='undefined'){
                  subtotale[groupid] = new Array();
              }
              if (typeof subtotale[groupid][index2] =='undefined'){
                  subtotale[groupid][index2] = 0;
              }
              if (typeof totale['Totale'][index2] =='undefined'){ totale['Totale'][index2] = 0; }
              
              if(val2 != null && typeof val2 == 'string'){

              valore = Number(val2.replace('%',"").replace(',',""));
              //console.log(valore);
              subtotale[groupid][index2] += valore;
              totale['Totale'][index2] += valore;
            }
            });
        });                
      $('tbody').find('.group').each(function (i,v) {
        var subtd = '';                      
        subtd = '<span class="fontbold">'+subtotale[i]['qty_ordered']+'</span>';
        $(this).find('td').eq(7).append(subtd);

        subtd = '<span class="fontbold">'+subtotale[i]['qty'].toFixed(3)+'</span>';
        $(this).find('td').eq(8).append(subtd);

        /*subtd = '<span class="fontbold">'+subtotale[i]['quantity_received']+'</span>';
        $(this).find('td').eq(8).append(subtd);*/

        subtd = '<span class="fontbold">'+subtotale[i]['buying_price'].toFixed(3)+'</span>';
        $(this).find('td').eq(11).append(subtd);

        subtd = '<span class="fontbold">'+subtotale[i]['buying_price_in_thb'].toFixed(3)+'</span>';
        $(this).find('td').eq(13).append(subtd);

        subtd = '<span class="fontbold">'+subtotale[i]['total_buying_price'].toFixed(3)+'</span>';
        $(this).find('td').eq(14).append(subtd);

        /*subtd = '<span class="fontbold">'+subtotale[i]['pod_import_tax_book'].toFixed(2)+'%</span>';
        $(this).find('td').eq(11).append(subtd);*/

        subtd = '<span class="fontbold">'+subtotale[i]['pod_freight'].toFixed(2)+'</span>';
        $(this).find('td').eq(16).append(subtd);

        subtd = '<span class="fontbold">'+subtotale[i]['pod_landing'].toFixed(2)+'</span>';
        $(this).find('td').eq(17).append(subtd);

        subtd = '<span class="fontbold">'+subtotale[i]['book_tax'].toFixed(2)+'</span>';
        $(this).find('td').eq(18).append(subtd);

        subtd = '<span class="fontbold">'+subtotale[i]['weighted'].toFixed(2)+'%</span>';
        $(this).find('td').eq(19).append(subtd);

        subtd = '<span class="fontbold">'+subtotale[i]['actual_tax'].toFixed(2)+'</span>';
        $(this).find('td').eq(20).append(subtd);

        subtd = '<span class="fontbold">'+subtotale[i]['actual_tax_percent'].toFixed(2)+'%</span>';
        $(this).find('td').eq(21).append(subtd);
      }); 
    }
  });

  $('.dataTables_filter input').unbind();
  $('.dataTables_filter input').bind('keyup', function(e) {
  if(e.keyCode == 13) {
    table2.search($(this).val()).draw();
  }
  });

  $(document).on("dblclick",".inputDoubleClick",function(){
      $(this).addClass('d-none');
      $(this).next().removeClass('d-none');
      $(this).next().focus();
  });

   $(document).on("dblclick",".fieldFocus",function(){
    $(this).removeAttr('disabled');
    //$(this).addClass('active');
    $(this).removeAttr('readonly');
    $(this).focus();
  });

  // to make that field on its orignal state
  $(document).on('keyup focusout','.fieldFocus',function(e) { 
    var fieldvalue = $(this).data('fieldvalue');
    if (e.keyCode == 27) {
      var thisPointer = $(this);
      thisPointer.val(fieldvalue);
      thisPointer.attr('disabled','true');
      thisPointer.attr('readonly','true');
      }
    if(e.keyCode == 13 || e.which == 0){
      
      if($(this).val() == '' || $(this).val() == fieldvalue)
      {
        return false;
      }
      else
      {
        var pod_id= $(this).data('id');    
        var thisPointer = $(this);
        saveSupData(thisPointer,thisPointer.attr('name'), thisPointer.val(),pod_id);
        $(this).data('fieldvalue',thisPointer.val());
      }
      $(this).attr('disabled','true');
      $(this).attr('readonly','true');
    } 
  });

  $(document).on('keyup focusout', '.po_group_data', function(e){
    var fieldvalue = $(this).data('fieldvalue');
    if (e.keyCode == 27) {
      var thisPointer = $(this);
      thisPointer.val(fieldvalue);
      return false;
      }

    if(e.keyCode == 13 || e.which == 0){
      /*The Below Code is necessary because we need to calculate freight and landing which is based on total gross weight*/
      var total_gross_weight = $('#po_group_total_gross_weight').val();
      if(total_gross_weight == 0 && ($(this).attr('name') == 'freight' || $(this).attr('name') == 'landing')){
        swal('Please Enter Total Gross Weight First!');
        $(this).val(fieldvalue);
        return false;
      }

      /*The Below Code is necessary because we need to calculate Actual tax which is based on total Import tax book*/
      var total_gross_weight = $('#po_group_import_tax_book').val();
      if(total_gross_weight == 0 && ($(this).attr('name') == 'tax')){
        swal('Please Enter Import Tax Book First!');
        $(this).val(fieldvalue);
        return false;
      }
      if($(this).val() == '' || $(this).val() == fieldvalue){
        return false;
      }
      else{
      var gId = "{{$po_group->id}}";
      var attr_name = $(this).attr('name');
      var new_value = $(this).val();
      //alert(gId);
      $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });

      $.ajax({
          method: 'post',
          url: '{{route("save-group-data")}}',
          data:'gId='+gId+'&'+attr_name+'='+new_value,
          success: function (data) {
            if(data.success == true){
            toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});              
              window.location.reload();
            }
          }
      });    
    }
  }
  });

  // confirm po button code here
  $(document).on('click','.confirm-to-stock-btn', function(e){
    var actual_tax = $("input[name=tax]").val();
    var freight = $("input[name=freight]").val();
    var landing = $("input[name=landing]").val();
    if(actual_tax == '')
    {
      swal({ html:true, title:'Alert !!!', text:'<b>Must Fill Actual Tax !</b>'});
      return false;
    }

    if(freight == '')
    {
      swal({ html:true, title:'Alert !!!', text:'<b>Must Fill Freight !</b>'});
      return false;
    }
    if(landing == '')
    {
      swal({ html:true, title:'Alert !!!', text:'<b>Must Fill Landing !</b>'});
      return false;
    }

    var id = $(this).data('id');   //po_Group id
    swal({
      title: "Are you sure!!!",
      text: "You want to confirm to stock?",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, confirm it!",
      cancelButtonText: "Cancel",
      closeOnConfirm: true,
      closeOnCancel: false
      },
    function (isConfirm) {
      if (isConfirm) {
        $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
        });
        
        $.ajax({
          method:"post",
          data:'id='+id,
          url: "{{ route('confirm-po-group') }}",
          success: function(response){
              if(response.success === true){
              toastr.success('Success!', 'Cost Confirmed Successfully.',{"positionClass": "toast-bottom-right"});              
              window.location.href = "{{ url('importing/incompleted-po-groups')}}";              
            }
          },
          error: function (request, status, error) {
          swal("Something Went Wrong! Contact System Administrator!");
            $('.form-control').removeClass('is-invalid');
            $('.form-control').next().remove();
            json = $.parseJSON(request.responseText);
            $.each(json.errors, function(key, value){
                $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                 $('input[name="'+key+'"]').addClass('is-invalid');
            });
        }
        });
      }
      else 
      {
        swal("Cancelled", "", "error");
      }
    });
  });

    // export pdf code
  $(document).on('click', '.export-pdf', function(e){

    var po_group_id = $('#po_group_id').val(); 
    $('.export-group-form')[0].submit();
    /*
    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
        });        
    $.ajax({
      method:"post",
      data:'po_group_id='+po_group_id,
      url: "{{ route('export-group-to-pdf') }}",
      success: function(response){
          if(response.success === true){
          toastr.success('Success!', 'Product Received Into Stock Successfully.',{"positionClass": "toast-bottom-right"});              
          window.location.href = "{{ url('/importing')}}";              
        }
      }
    });*/

  });

      // export pdf2 code
  $(document).on('click', '.export-pdf2', function(e){

    var po_group_id = $('#po_group_id').val(); 
    $('.export-group-form2')[0].submit();
    /*
    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
        });        
    $.ajax({
      method:"post",
      data:'po_group_id='+po_group_id,
      url: "{{ route('export-group-to-pdf') }}",
      success: function(response){
          if(response.success === true){
          toastr.success('Success!', 'Product Received Into Stock Successfully.',{"positionClass": "toast-bottom-right"});              
          window.location.href = "{{ url('/importing')}}";              
        }
      }
    });*/

  });

});


  function saveSupData(thisPointer,field_name,field_value,pod_id){   
    var po_group_id = $('#po_group_id').val();   
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
        method: "post",
        url: "{{ url('importing/edit-po-group-details') }}",
        dataType: 'json',
        // data: {field_name:field_name,field_value:field_value,supplier_id:supplier_id,tag_index:tag_index},
        data: 'pod_id='+pod_id+'&'+field_name+'='+field_value+'&po_group_id='+po_group_id,
        beforeSend: function(){
          // shahsky here
        },
        success: function(data)
        {
          if(data.gross_weight == true)
          {
            $('#po_group_total_gross_weight').val(data.po_group.po_group_total_gross_weight);
            $(".product_table").DataTable().ajax.reload(null, false );
            toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
            return true;
          }
          if(data.import_tax == true)
          {
            $('#po_group_import_tax_book').val(data.po_group.po_group_import_tax_book);
            $(".product_table").DataTable().ajax.reload(null, false );
            toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
            return true;
          }
          if(data.success == true)
          {
            $(".product_table").DataTable().ajax.reload(null, false );
            toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
            return true;
          }
          else if(data.success == false){
            var extra_quantity = data.extra_quantity;
            $.ajaxSetup({
                  headers: {
                      'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                  }
              });
            $.ajax({
              type: "post",
              url: "{{ route('get-incomplete-pos') }}",
              data: 'pod_id='+pod_id+'&extra_quantity='+extra_quantity,
              
              success: function(response){
                if(response.success == true){
                  $('#greater_quantity').modal('show');
                  $('.fetched-po').html(response.html_string);
                }
                else{
                  $(".product_table").DataTable().ajax.reload(null, false );
                  if(response.extra_quantity == true){
                  swal('The QTY You Entered Cannot be divided accordingly');                    
                  }
                  else{                    
                  swal('You Cannot Enter QTY Greater Than the Required QTY');
                  }
                }
              }
            });

           
          }
        },
         error: function (request, status, error) {
          swal("Something Went Wrong! Contact System Administrator!");
            $('.form-control').removeClass('is-invalid');
            $('.form-control').next().remove();
            json = $.parseJSON(request.responseText);
            $.each(json.errors, function(key, value){
                $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                 $('input[name="'+key+'"]').addClass('is-invalid');
            });
        }

      });
  }
</script>
@stop

