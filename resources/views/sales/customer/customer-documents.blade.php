@extends('sales.layouts.layout')

@section('title','Customer Management | Supply Chain')

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

{{-- Content Start from here --}}

  <!-- header starts -->
  <div class="row d-flex align-items-center left-right-padding mb-2">
    <div class="col-lg-7">
      <h3>{{@$customer->company}} @if(!array_key_exists('general_document', $global_terminologies)) General Documents @else {{$global_terminologies['general_document']}} @endif</h3>
    </div>
  </div>

  <!-- header ends -->
<div class="row entriestable-row">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
      <div class="table-responsive">
      	<table class="table entriestable table-bordered table-general-documents text-center" style="border-bottom: none;width: 100%">
       <thead>
                  <tr>
                      <th>Description</th>
                      <th>File Name</th>
                      <th>Date</th>
                      <th>Action</th>
                  </tr>
              </thead>
 
    </table>
      </div>  
    </div>
  </div>
</div>

<!-- </div> -->
<!--  Content End Here -->


<!-- Loader Modal -->
<div class="modal" id="loader_modal_old" role="dialog">
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
</div><!-- main content end here -->

@endsection

@section('javascript')
<script type="text/javascript">
//General documents table
$(function(e){
  var id = "{{$id}}";
  // alert(id);
   $(document).on("focus", ".datepicker", function(){
        $(this).datetimepicker({
            timepicker:false,
            format:'Y-m-d'});
    });
     $('.table-general-documents').DataTable({
         processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
          "autoWidth": false,
          "scrollX": true,
          
        ordering: false,
        serverSide: true,
         "lengthMenu":[100,200,300,400],
         columnDefs: [
            { width: '10%', targets: 0 },
            { width: '30%', targets: 1 },
            { width: '30%', targets: 2 },
            { width: '30%', targets: 3 },
        ],
        fixedColumns: true,
         ajax: {
            url:"{!! route('get-customer-general-documents') !!}",
            data: function(data) { data.id = id,data.al = true } ,
            },
        columns: [
            { data: 'description', name: 'description' },
            { data: 'file_name', name: 'file_name' },
            { data: 'date', name: 'date' },
            { data: 'action', name: 'action' },
              ]
    });

     // Delete General Documents
$(document).on('click', '.deleteGeneralDocument', function(e){

    var id = $(this).data('id');
      swal({
        title: "Are you sure?",
        text: "You want to delete the document ?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
        closeOnConfirm: true,
        closeOnCancel: false
        },
      function (isConfirm) {
          if (isConfirm) {
            $.ajax({
              method:"get",
              data:'id='+id,
              url: "{{ route('delete-customer-general-document') }}",
              success: function(response){
                if(response.success === true){
                  toastr.success('Success!', 'Document Deleted Successfully.',{"positionClass": "toast-bottom-right"});
                  $('.table-general-documents').DataTable().ajax.reload();
                }
              }
            });
          }
          else {
            swal("Cancelled", "", "error");
          }
      });
    });

});
</script>
@stop

