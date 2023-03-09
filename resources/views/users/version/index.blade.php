@extends('backend.layouts.layout')
@section('title','Versions')


@section('content')

<div class="row align-items-center mb-3">
    <div class="col-md-10 title-col">
      <h4 class="maintitle">Version List</h4>
    </div>
    {{-- @if(Auth::user()->role_id == 8 || Auth::user()->role_id == 10)
    <div class="col-md-2 text-right">
        <a href="{{route('create-version')}}" class="btn button-st">Create Version</a>
    </div>
    @endif --}}
</div>
  

<div class="row entriestable-row mt-2">
  <div class="col-12">
    <div class="bg-white custompadding customborder">

      
        <div class="gemstonetable"> 
      
            <div class="table-responsive">
                <table class="table entriestable table-bordered table-versions text-center w-100">
                    <thead class="text-center">
                        <tr>
                        <th>Action</th>
                        <th>Version</th>
                        <th>Status</th>   
                        </tr>
                    </thead>
                </table>

            </div>
        
        </div> 

    </div>
  </div>
</div>


<!--  Content End Here -->

<!--  Roles Adding Modal Start Here -->
{{-- <div class="modal fade" id="addrolemodal">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">   
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div> 
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Add Role</h3>
          <div class="mt-5">
          {!! Form::open(['method' => 'POST', 'class' => 'add-role-form']) !!}
            <div class="form-group">
              {!! Form::text('name', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Role Name']) !!}
            </div>
            <div class="form-group">
              {!! Form::text('role_privilege', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Role Privilege ']) !!}
            </div>
            
            <div class="form-submit">
              <input type="submit" value="add" class="btn btn-bg save-btn">
              <input type="reset" value="close" class="btn btn-danger close-btn">
            </div>
          {!! Form::close() !!}
         </div> 
        </div>
      </div>
    </div>
  </div> --}}


@endsection

@section('javascript')
<script type="text/javascript">
    $(function(e){
       var table2 = $('.table-versions').DataTable({
            processing: true,
            "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
            ordering: false,
            serverSide: true,
            pageLength: {{100}},
            scrollX: true,
            scrollY : '90vh',
            scrollCollapse: true,
            lengthMenu: [ 100, 200, 300, 400],
            ajax: "{!! route('get-version') !!}",
            columns: [
              { data: 'action', name: 'action' },
              { data: 'version', name: 'version' },
              { data: 'status', name: 'status' }
            ] 
        });

        $(document).on('click','.delete-version',function(e){
            var id = $(this).attr("data-id");
            swal({
            title: "Alert!",
            text: "Are you sure you want to Delete this Version?",
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
                    data:{id:id},
                    url:"{{ route('delete-version') }}",
                    beforeSend:function(){
                    $('#loader_modal').modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                    $("#loader_modal").modal('show');
                    },
                    success:function(data){
                    $("#loader_modal").modal('hide');
                        if(data.error == false){
                        toastr.success('Success!', data.successmsg ,{"positionClass": "toast-bottom-right"});
                        $('.table-versions').DataTable().ajax.reload();              
                        }
                    }
                    });
                } 
                else{
                    swal("Cancelled", "", "error");
                }
            });
        });

        $(document).on('click','.btn-version',function(e){
            var id = $(this).attr("data-id");
            swal({
            title: "Alert!",
            text: "Are you sure you want to Publish this Version? You won't be able to undo this.",
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
                    data:{id:id},
                    url:"{{ route('publish-version') }}",
                    beforeSend:function(){
                    $('#loader_modal').modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                    $("#loader_modal").modal('show');
                    },
                    success:function(data){
                    $("#loader_modal").modal('hide');
                        if(data.error == false){
                        toastr.success('Success!', data.successmsg ,{"positionClass": "toast-bottom-right"});
                        $('.table-versions').DataTable().ajax.reload();              
                        }
                    }
                    });
                } 
                else{
                    swal("Cancelled", "", "error");
                }
            });
        });

        // $(document).on('click','.edit-icon',function(e){
        //     var id = $(this).attr("data-id");
        //     alert(id);
        // });

        // $(document).on('click','.view-icon',function(e){
        //     var id = $(this).attr("data-id");
        //     alert(id);
        // });
    });
    @if(Session::has('success'))
      toastr.success('Success!',"{{ Session::get('success')}}",{" positionClass": "toast-bottom-right"});  
      @php 
      Session()->forget('success');     
      @endphp  
    @endif
    @if(Session::has('error'))
      toastr.error('Warning!',"{{ Session::get('error')}}",{" positionClass": "toast-bottom-right"});  
      @php 
      Session()->forget('error');     
      @endphp  
    @endif
</script>
@endsection
