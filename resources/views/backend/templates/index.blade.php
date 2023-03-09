@extends('backend.layouts.layout')

@section('title','Email Templates | Supply Chain')

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
        @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 3 || Auth::user()->role_id == 4)
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
          <li class="breadcrumb-item active">Email Template</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}
{{-- <div class="row mb-0">
  <div class="col-md-12 title-col">
    <div class="d-sm-flex justify-content-between">
      <h4 class="text-uppercase fontbold">EMAIL TEMPLATES</h4> 
        <div class="mb-0">
        <a  href="{{ route('create-template') }}" class="btn button-st btn-wd" >ADD EMAIL TEMPLATE</a>
        </div>
    </div>
  </div>
</div> --}}
<div class="row align-items-center mb-3">
  <div class="col-md-8 title-col">
      <h4 class="maintitle">EMAIL TEMPLATES</h4>
  </div>    
      <div class="col-md-4 text-right">
         <a  href="{{ route('create-template') }}" class="btn button-st btn-wd btn_add_email">ADD EMAIL TEMPLATE</a>
  </div>
</div>

<div class="row entriestable-row mt-2">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">

    <div class="table-responsive">
          <table class="table entriestable table-bordered table-template">
              <thead class="text-center">
                  <tr>
                      <th>Action</th>
                      <th>@if(!array_key_exists('type', $global_terminologies)) Type @else {{$global_terminologies['type']}} @endif</th>
                      <th>Subject</th>
                      <th>Last Updated By</th>
                      <th>Last Updated At</th>
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

@endsection

@section('javascript')
<script type="text/javascript">
  $(function(e){
    var table2 = $('.table-template').DataTable({
      "sPaginationType": "listbox",
         processing: false,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        ordering: false,
        serverSide: true,
        pageLength: {{100}},
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
          url:"{!! route('get-template') !!}",
        },
        columns: [
            { data: 'action', name: 'action' },
            { data: 'type', name: 'type' },
            { data: 'subject', name: 'subject' },
            { data: 'users.name', name: 'users.name' },
            { data: 'updated_at', name: 'updated_at' },
            { data: 'status', name: 'status' }
        ],
      drawCallback: function(){
      $('#loader_modal').modal('hide');
    },
      });

      $('.dataTables_filter input').unbind();
        $('.dataTables_filter input').bind('keyup', function(e) {
        if(e.keyCode == 13) {
          // alert();
                table2.search($(this).val()).draw();
        }
      });

      $(document).on('click','.btn_add_email',function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      });

      @if(Session::has('successmsg'))
          toastr.success('Success!', "{{ Session::get('successmsg') }}",{"positionClass": "toast-bottom-right"});        
      @endif
    
  });
</script>
@stop

