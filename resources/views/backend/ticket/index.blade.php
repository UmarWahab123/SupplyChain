@extends('backend.layouts.layout')
@section('title','Tickets')
<style type="text/css">
.invalid-feedback {
     font-size: 100%; 
}
.disabled:disabled{
  opacity:0.5;
  cursor: not-allowed; 
}
</style>
@section('content')

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
          <li class="breadcrumb-item active">Tickets</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}
<div class="row mb-3">
  <div class="col-md-8 title-col">
    <h3 class="maintitle text-uppercase fontbold">Tickets</h3>
  </div>
 
</div>
<div class="row entriestable-row mt-3">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
    <div class="d-sm-flex justify-content-between">
      <h4>
        @if(Auth::user()->roles->name == "Admin")
        My Tickets
        @else
        All Tickets
        @endif
      </h4>
      <div class="mb-3">
        <a class="btn create-ticket" href="javascript:void(0);"  >
          Create Ticket
        </a>
      </div>
    </div>

    <div class="table-responsive">
          <table class="table entriestable table-bordered text-center table-ticket" >
              <thead>
                  <tr>
                      <th>Action</th>
                      <th>Ref #</th>
                      <th>Title</th>
                      <th>Raised By</th>
                      <th>Department</th>
                      {{-- <th width="15%">Priority</th> --}}
                      <th width="15%">Status</th>
                      <th>Created At</th>
                      {{-- <th>Updated At</th> --}}
                  </tr>
              </thead>
              <tbody>
                
                @foreach($tickets as $ticket)
                  <tr>
                    <td>
                      {{-- {{dd(Auth::user()->roles->name)}} --}}
                      <div class="icons align-items-center d-flex justify-content-center actionIcons">
                        <a href="{{route('ticket-info',['ref' => $ticket['ticket_ref']])}}" class="actionicon viewIcon" data-id="" title="View Ticket"><i class="fa fa-eye"></i></a>
                      </div>
                     {{--  @if(Auth::user()->roles->name == "Vendor" || Auth::user()->roles->name == "Account Executive")
                      <a href="{{route('ticket-info',['ref' => $ticket['ticket_ref']])}}" class="actionicon viewIcon" data-id="" title="View Ticket"><i class="fa fa-eye"></i></a>
                      @if(Auth::user()->roles->name == "Partner")
                      <a href="{{route('partner-ticket-info',['ref' => $ticket['ticket_ref']])}}" class="actionicon viewIcon" data-id="" title="View Ticket"><i class="fa fa-eye"></i></a>
                      @endif
                      @if(Auth::user()->roles->name == "Sales Company")
                      <a href="{{route('ticket-info',['ref' => $ticket['ticket_ref']])}}" class="actionicon viewIcon" data-id="" title="View Ticket"><i class="fa fa-eye"></i></a>
                      @endif
                      @if(Auth::user()->roles->name == "Logistics Company")
                      <a href="{{route('logistic-ticket-info',['ref' => $ticket['ticket_ref']])}}" class="actionicon viewIcon" data-id="" title="View Ticket"><i class="fa fa-eye p-1"></i></a>
                      @endif
                      @if(Auth::user()->roles->name == "Lab")
                      <a href="{{route('lab-ticket-info',['ref' => $ticket['ticket_ref']])}}" class="actionicon viewIcon" data-id="" title="View Ticket"><i class="fa fa-eye"></i></a>
                      @endif
                      @if(Auth::user()->roles->name == "Admin")
                      <a href="{{route('admin-ticket-info',['ref' => $ticket['ticket_ref']])}}" class="actionicon viewIcon" data-id="" title="View Ticket"><i class="fa fa-eye"></i></a>
                      @endif --}}
                    </td>
                    <td>{{$ticket['ticket_ref']}}</td>
                    <td>{{$ticket['title']}}</td>
                    <td>
                      @php
                      $user = App\User::where('email',$ticket['notification_email'])->first()
                      @endphp
                      @if($user)
                      {{$user->name.' ('.$user->roles->name.')'}}
                      @else
                      N/A
                      @endif
                    </td>
                    <td>{{$ticket['department_name']}}</td>

                   {{--  <td>
                    @if($ticket['Priority'] == 'Urgent')
                      <span class="badge badge-pill badge-danger font-weight-normal pb-2 pl-3 pr-3 pt-2">Urgent</span>
                    @endif
                    @if($ticket['Priority'] == 'High')
                      <span class="badge badge-pill badge-warning font-weight-normal pb-2 pl-3 pr-3 pt-2" style="font-size:1rem;">High</span>
                    @endif
                    @if($ticket['Priority'] == 'Medium')
                      <span class="badge badge-pill badge-secondary font-weight-normal pb-2 pl-3 pr-3 pt-2" style="font-size:1rem;">Medium</span>
                    @endif
                    @if($ticket['Priority'] == 'Low')
                      <span class="badge badge-pill badge-dark font-weight-normal pb-2 pl-3 pr-3 pt-2" style="font-size:1rem;">Low</span>
                    @endif
                    </td> --}}
                    <td>
                    @if($ticket['status'] == "pending")
                      <span class="badge badge-pill badge-warning font-weight-normal pb-2 pl-3 pr-3 pt-2">Pending</span>
                    @endif
                    @if($ticket['status'] == 'in_progress')
                      <span class="badge badge-pill badge-info font-weight-normal pb-2 pl-3 pr-3 pt-2">In Progress</span>
                    @endif
                     @if($ticket['status'] == 'on_hold')
                      <span class="badge badge-pill badge-secondary font-weight-normal pb-2 pl-3 pr-3 pt-2">On Hold</span>
                    @endif
                    @if($ticket['status'] == 'completed')
                      <span class="badge badge-pill badge-success font-weight-normal pb-2 pl-3 pr-3 pt-2">Completed</span>
                    @endif
                    </td>
                    <td>{{date('F jS, Y h:i A', strtotime($ticket['creation_date']))}}</td>

                  </tr>
                @endforeach
                
              </tbody>
               
          </table>
        </div>  
        </div>
    
  </div>
</div>

</div>
@endsection

@section('javascript')
<script type="text/javascript">
  
  $(function(e){

    $(document).on("focus", ".datepicker", function(){
        $(this).datetimepicker({
            timepicker:false,
            format:'Y-m-d'});
    });
    $('.table-ticket').DataTable({
      pageLength: 100,
      lengthMenu: [100,200,300,400,500],
      "ordering":false,
      initComplete: function () {
        this.api().columns([5]).every(function () {
          var column = this;
          var select = document.createElement("select");
          // select.setAttribute('class', 'selectpicker');
          $(select).append('<option value="">All</option><option value="pending">Pending</option><option value="In Progress">In Progress</option><option value="On Hold">On Hold</option><option value="completed">Completed</option>');
          $(select).addClass('form-control');
          $(select).appendTo($(column.header()))
          .on('change', function () {
            column.search($(this).val()).draw();
          });
        });

      }
    });

    $(document).on('keyup', '.form-control', function(){
      $(this).removeClass('is-invalid');
      $(this).next().remove();
    });
  });

</script>
@stop
