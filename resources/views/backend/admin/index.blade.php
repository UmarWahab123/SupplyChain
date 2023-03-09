@extends('backend.layouts.layout')

@section('title','POS Integration | Supply Chain')
@section('content')
<link href="//cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@4/dark.css" rel="stylesheet">
<style type="text/css">
.invalid-feedback {
     font-size: 100%;
}
.disabled:disabled{
  opacity:0.5;
  cursor: not-allowed;
}

</style>

<div class="row align-items-center mb-3 d-flex justify-content-between">
    <div class="col-md-8 title-col">
        <h4 class="maintitle">POS Integeration</h4>
    </div>
    <div class="col-2">
        <select name="status_id" id="" class="form-control mr-3 status_id">
            <option value='1'>Active</option>
            <option value='0'>InActive</option>
        </select>
    </div>
</div>

<div class="row entriestable-row mt-2">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">

    <div class="table-responsive">
          <table class="table entriestable table-bordered table-posIntegeration text-center">
              <thead>
                  <tr>
                    <th>Action</th>
                      <th>Device Name</th>
                      <th>Warehouse</th>
                      <th>Status</th>
                      <th>token</th>
                      <th>Created At</th>
                  </tr>
              </thead>

          </table>
        </div>
        </div>

  </div>
</div>

</div>
<!--  Content End Here -->
@endsection

@section('javascript')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<script type="text/javascript">
;
  $(function(e){

    $(document).on('change','.status_id',function(){

    var selected = $(this).val();
    $('.table-posIntegeration').DataTable().ajax.reload();
  });

    var table = $('.table-posIntegeration').DataTable({
        processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '
        },
        ordering: false,
        serverSide: false,
        ajax:{
            ajax: "{!! route('pos-integeration') !!}",
            data: function(data) {data.status_id = $('.status_id option:selected').val()} ,
        },
        columns: [
            { data: 'action', name: 'action' },
            { data: 'device_name', name: 'device_name' },
            { data: 'warehouse_name', name: 'warehouse_name' },
            { data: 'status', name: 'status' },
            { data: 'token', name: 'token' },
            { data: 'created_at', name: 'created_at' },
        ],

    });
  });
  $(document).on('click','.editIcon',function(){
    let id = $(this).data('id');
    let status = $(this).data('pos_status');
    // alert(status);
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.isConfirmed) {
                    $.ajax({
                        type: "GET",
                        url: "{{route('pos-integeration-update-status')}}",
                        data: {id:id,status:status},
                        dataType: "JSON",
                        success: function (response) {
                            if(response.status==200)
                            {
                                $('.table-posIntegeration').DataTable().ajax.reload();
                                toastr.success('Success!', 'Status updated successfully',{"positionClass": "toast-bottom-right"});
                            }
                        }
                    });
                }
            });
  });
</script>
@stop

