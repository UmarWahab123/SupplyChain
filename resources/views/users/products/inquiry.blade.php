@extends('users.layouts.layout')

@section('title','Products Management | Purchasing')

@section('content')
<style type="text/css">
.invalid-feedback {
     font-size: 100%;
}
.disabled:disabled{
  opacity:0.5;
  cursor: not-allowed;
}
</style>

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
          <li class="breadcrumb-item active">Inquiry Products</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}
<div class="row align-items-center mb-3">
  <div class="col-md-8 title-col">
    <h3 class="maintitle text-uppercase fontbold">Inquiry Products</h3>
  </div>
</div>
@if(Auth::user()->role_id != 7)
<div class="d-sm-flex justify-content-between">
  <div class="delete-selected-item d-none">
    <a href="javascript:void(0);" class="btn selected-item-btn btn-sm success-btn move-to-inventory" data-type="quotation" title="Move to Inventory" >
    <img src="{{ asset('public\site\assets\purchasing\img\move.png') }}" alt="move to inventory" style="width:30px; height:30px;"></a>

    <a href="javascript:void(0);" class="btn selected-item-btn btn-sm success-btn delete_inquiry_products p-2" data-type="quotation" title="Revert Inquiry Products" >
    <i class="fa fa-undo" style="font-size: 25px;"></i></a>
  </div>
</div>
@endif
<div class="row entriestable-row">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
    <div class="table-responsive">
      <table class="table entriestable table-bordered table-inquiry-products text-center">
        <thead>
          <tr>
            <th class="noVis">
              <div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                <input type="checkbox" class="custom-control-input check-all" name="check_all" id="check-all">
                <label class="custom-control-label" for="check-all"></label>
              </div>
            </th>
            <th class="long_length">{{$global_terminologies['our_reference_number']}}</th>
            <th>Default Supplier</th>
            <th width="20%">{{$global_terminologies['product_description']}}</th>
            <th>{{$global_terminologies['pieces']}}</th>
            <th>{{$global_terminologies['qty']}}</th>
            <th>Default Price</th>
            <th>{{$global_terminologies['category']}}</th>

            <th>Added By</th>
            <th>Quotation</th>
          </tr>
        </thead>

      </table>
    </div>
    </div>
  </div>
</div>

</div>

{{-- Category modal--}}
<div class="modal" id="addCategoryModal">
  <div class="modal-dialog modal-lg modal-dialog-centered parcelpop">
    <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal">×</button>
    </div>

    <div class="modal-body text-center">
      <h3 class="text-capitalize fontmed">Add Category</h3>
      <div class="mt-4">

        <div class="form-group">
        <select name="category" id="category" class="font-weight-bold form-control-lg form-control">
          <option value="" selected disabled>Choose Category</option>
          @foreach($categories as $category)
          <option value="{{ $category->id }}">{{$category->title}}</option>
          @endforeach
        </select>
        </div>

        <div class="form-group">
          <select name="sub_category" id="sub_category" class="font-weight-bold form-control-lg form-control fill_sub_cat_div">
          <option value="" readonly disabled>Choose Sub Category</option>
          </select>
        </div>

        <div class="form-submit">
          <input type="button" value="add" class="btn btn-bg add-category-btn">
          <input type="reset" value="close" data-dismiss="modal"  class="btn btn-danger close-btn">
        </div>
     </div>
    </div>

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
  $(document).ready(function(){
    var ref = '{{$global_terminologies['our_reference_number']}}';
    var words = $.trim(ref).split(" ");
    var newName = [];
    if(words.length > 1){
      for(var i=0; i< words.length; i++){
        if(i > 0){
          newName.push("<br />");
        }
        newName.push(words[i]);
      }
      ref = newName.join("");
    }
    $('.long_length').html(ref);
  });
  $(function(e){

    $(document).on("focus", ".datepicker", function(){
      $(this).datetimepicker({
        timepicker:false,
        format:'Y-m-d'});
    });

    var table2 = $('.table-inquiry-products').DataTable({
      drawCallback: function()
      {
        $('.state-tags').select2();
        $('.incomp-select2').select2({dropdownCssClass : 'bigdrop'});
      },
      "columnDefs": [
        { className: "dt-body-left", "targets": [ 1,2,3,4,5,7,8,9] },
        { className: "dt-body-right", "targets": [6] }
      ],
      processing: false,
      "language": {
        processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      // "pagingType":"input",
      "sPaginationType": "listbox",
        ordering: false,
      serverSide: true,
      pageLength: {{100}},
      scrollX: true,
      scrollY : '90vh',
      scrollCollapse: true,
      lengthMenu: [ 100, 200, 300, 400],
      ajax: {
           beforeSend: function(){
          $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
          $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
          $("#loader_modal").modal('show');
        },
        url:"{!! route('get-inquiry-products-to-purchasing') !!}",
      },
        columns: [
        { data: 'checkbox', name: 'checkbox' },
        { data: 'reference_no', name: 'reference_no' },
        { data: 'supplier', name: 'supplier' },
        { data: 'short_desc', name: 'short_desc' },
        { data: 'pieces', name: 'pieces' },
        { data: 'qty', name: 'qty' },
        { data: 'default_price', name: 'default_price' },
        { data: 'category_id', name: 'category_id' },
        { data: 'added_by', name: 'added_by' },
        { data: 'quotation_no', name: 'quotation_no' },
      ],
      initComplete: function () {
        $('body').find('.dataTables_scrollBody').addClass("scrollbar");
        $('body').find('.dataTables_scrollHead').addClass("scrollbar");
        if("{{Auth::user()->role_id}}" == 7)
        {
          $('.inputDoubleClick').removeClass('inputDoubleClick');
          $('.selectDoubleClick').removeClass('selectDoubleClick');
        }

      },
      drawCallback: function(){
        $('#loader_modal').modal('hide');
      },
    });

    $('.dataTables_filter input').unbind();
    $('.dataTables_filter input').bind('keyup', function(e) {
      if(e.keyCode == 13)
      {
        table2.search($(this).val()).draw();
      }
    });

    // double click select
    $(document).on("dblclick",".selectDoubleClick",function(){
        $x = $(this);
          $(this).addClass('d-none');
          $(this).after('<span class="spinner"><i class="fa fa-spinner"></i></span>');

        setTimeout(function(){

          $('.spinner').remove();
          $x.next().removeClass('d-none');
          $x.next().addClass('active');
          $x.next().focus();
          var num = $x.next().val();
          $x.next().focus().val('').val(num);

         }, 300);

    });

    // dropdowns double click editable
    $(document).on("change",".selectFocus",function() {
      if($(this).attr('name') == 'category_id')
      {
        var new_value = $("option:selected", this).html();
        var p_cat_id = $("option:selected", this).val();
        var id = $(this).closest('tr').attr('id');
        var thisPointer = $(this);
        thisPointer.addClass('d-none');
        thisPointer.prev().removeClass('d-none');
        $(this).prev().html(new_value);
        saveInquiryData(id,thisPointer.attr('name'), thisPointer.val());
      }
    });

    function saveInquiryData(id,field_name,field_value){
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method: "post",
        url: "{{ url('save-inquiry-prod-data') }}",
        dataType: 'json',
        data: 'id='+id+'&'+field_name+'='+field_value,
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
         $("#loader_modal").modal('show');
        },
        success: function(data)
        {
          $("#loader_modal").modal('hide');
          if(data.success == true)
          {
            toastr.success('Success!', 'Category Assigned Successfully.',{"positionClass": "toast-bottom-right"});
            $('.table-inquiry-products').DataTable().ajax.reload();
          }
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }

      });
    }

    $(document).on('click', '.check-all', function (){
        if(this.checked == true){
        $('.check').prop('checked', true);
        $('.check').parents('tr').addClass('selected');
        var cb_length = $( ".check:checked" ).length;
        if(cb_length > 0){
          $('.delete-selected-item').removeClass('d-none');
        }
      }else{
        $('.check').prop('checked', false);
        $('.check').parents('tr').removeClass('selected');
        $('.delete-selected-item').addClass('d-none');
      }
    });

    $(document).on('click', '.check', function () {
        var cb_length = $( ".check:checked" ).length;
        var st_pieces = $(this).parents('tr').attr('data-pieces');
        if(this.checked == true){
        $('.delete-selected-item').removeClass('d-none');

        $(this).parents('tr').addClass('selected');
        }

      else{
        $(this).parents('tr').removeClass('selected');
        if(cb_length == 0){
         $('.delete-selected-item').addClass('d-none');
        }

      }
    });

    $('.move-to-inventory').on('click', function(e){
      // $('#addCategoryModal').modal('show');
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });

      var selected_products = [];
      $("input.check:checked").each(function(){
        selected_products.push($(this).val());
      });

      $.ajax({
        url: "{{ route('move-to-inventory') }}",
        method: 'post',
        data: {selected_products:selected_products},
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function (response) {
          $('#loader_modal').modal('hide');
          $('#addCategoryModal').modal('hide');
          $('.delete-selected-item').addClass('d-none');
          if(response.success == false)
          {
            toastr.error('Success!', response.errormsg ,{"positionClass": "toast-bottom-right"});
            $('.delete-selected-item').removeClass('d-none');
          }
          if(response.success == true)
          {
            toastr.success('Success!', response.successmsg ,{"positionClass": "toast-bottom-right"});
            if(response.rowsCount > 1)
            {
              $('.table-inquiry-products').DataTable().ajax.reload();
              $('.delete-selected-item').addClass('d-none');
              $('.check-all').prop('checked', false);
            }
            else
            {
              setTimeout(function(){
                window.location.href = "{{ url('get-product-detail') }}"+"/"+response.id;
              }, 500);
            }
          }

        },
        error: function(request, status, error){
          $('#loader_modal').modal('hide');
        }
      });
    });

    $(document).on('click', '.add-category-btn', function(){
      // $.ajaxSetup({
      //   headers: {
      //     'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      //   }
      // });

      // var selected_products = [];
      // $("input.check:checked").each(function(){
      //   selected_products.push($(this).val());
      // });

      // var category = $("#category").val();
      // var sub_category = $("#sub_category").val();

      // $.ajax({
      //   url: "{{ route('move-to-inventory') }}",
      //   method: 'post',
      //   data: {selected_products:selected_products,category:category,sub_category:sub_category},
      //   beforeSend: function(){
      //       $('#loader_modal').modal('show');
      //   },
      //   success: function (response) {
      //     $('#loader_modal').modal('hide');
      //     $('#addCategoryModal').modal('hide');
      //     $('.delete-selected-item').addClass('d-none');
      //     toastr.success('Success!', response.successmsg ,{"positionClass": "toast-bottom-right"});
      //     if(response.rowsCount > 1)
      //     {
      //       $('.table-inquiry-products').DataTable().ajax.reload();
      //     }
      //     else
      //     {
      //       setTimeout(function(){
      //         window.location.href = "{{ url('get-product-detail') }}"+"/"+response.id;
      //       }, 500);
      //     }

      //   }
      // });
    });

    $(document).on('click', '.delete_inquiry_products', function(e){
      var selected_products = [];
      $("input.check:checked").each(function() {
        selected_products.push($(this).val());
      });

      swal({
        title: "Alert!",
        text: "Are you sure you dont want to move this item to products catalog, selected item will be reverted as a Billed item ?",
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
            $.ajaxSetup({
              headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
              }
            });
            $.ajax({
              url: "{{ route('delete-inquiry-products') }}",
              method: 'post',
              data: {selected_products:selected_products},
              beforeSend: function(){
                $('#loader_modal').modal({
                  backdrop: 'static',
                  keyboard: false
                });
                $('#loader_modal').modal('show');
              },
              success: function (response) {
                $('#loader_modal').modal('hide');
                $('.delete-selected-item').addClass('d-none');
                toastr.success('Success!', 'Inquiry Products Revert Successfully!',{"positionClass": "toast-bottom-right"});
                $('.check-all').prop('checked', false);
                $('.table-inquiry-products').DataTable().ajax.reload();
              },
              error: function(request, status, error){
                $('#loader_modal').modal('hide');
              }
            });
        }
        else{
          swal("Cancelled", "", "error");
        }
      });

    });

    $(document).on('keyup', '.form-control', function(){
      $(this).removeClass('is-invalid');
      $(this).next().remove();
    });

    $(document).on('change',"#category",function(){
      var category_id=$(this).val();
      $.ajax({

          url:"{{route('filter-sub-category')}}",
          method:"get",
          dataType:"json",
          data:{category_id:category_id},
          beforeSend: function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('show');
          },
          success:function(data){
            $('#loader_modal').modal('show');
              var html_string = '';
                html_string+="<option value=''>Select a Sub Category</option>";
              for(var i=0;i<data.length;i++){
                html_string+="<option value='"+data[i]['id']+"'>"+data[i]['title']+"</option>";
              }
              // $("#state_div").remove();
              // store_sb_cat.after($("<div></div>").text(html_string));
              $(".fill_sub_cat_div").empty();
              $(".fill_sub_cat_div").append(html_string);

          },
          error:function(){
            $('#loader_modal').modal('show');
              alert('Error');
          }

      });
    });
  });
</script>
@stop

