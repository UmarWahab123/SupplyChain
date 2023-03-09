@extends('backend.layouts.layout')
@section('title','Ticket Info')
<style type="text/css">
.invalid-feedback {
     font-size: 100%; 
}
.disabled:disabled{
  opacity:0.5;
  cursor: not-allowed; 
}
#further_detail_modal .modal {
    display: block !important;
}
#further_detail_modal .modal-dialog{
      overflow-y: initial !important
}
#further_detail_modal .modal-body{
  height: 550px;
  overflow-y: auto;
}
</style>
@section('content')

{{-- Content Start from here --}}
<div class="row mb-3">
  <div class="col-md-8 title-col">
    <h3 class="maintitle text-uppercase fontbold">Ticket Detail</h3>
  </div>
 
</div>


<div class="row entriestable-row mt-3">
  <div class="col-8">
    <div class="card mb-3">
      {{-- <div class="card-header">
        Contact Information
      </div> --}}
      <div class="card-body">
        <div class="row">
          <div class="col-md-2 border-right">
            <h5>{{$ref}}</h5>
            @if($content['ticket_detail']['status'] == 'pending')
            <label class="btn btn-warning btn-sm">Pending</label>
            @endif
            @if($content['ticket_detail']['status'] == 'in_progress')
            <label class="btn btn-info btn-sm">In Progress</label>
            @endif
            @if($content['ticket_detail']['status'] == 'on_hold')
            <label class="btn btn-secondary btn-sm">On Hold</label>
            @endif
            @if($content['ticket_detail']['status'] == 'completed')
            <label class="btn btn-success btn-sm">Completed</label>
            @endif
          </div>
          <div class="col-md-10">
            <blockquote class="blockquote mb-0">
              <p>{{$content['ticket_detail']['title']}}</p>
              {!! substr(strip_tags($content['ticket_detail']['detail']),0, 120) !!}@if(substr(strip_tags($content['ticket_detail']['detail']),120, 5000))<a href="javascript:void(0)" class="badge badge-info further_detail">For Further Detail</a>@endif
              
              {{-- <p>{!! substr(strip_tags($content['ticket_detail']['detail']),0, 120) !!}@if(substr(strip_tags($content['ticket_detail']['detail']),120, 5000))<span class="collapse" id="viewdetails3">{!! substr(strip_tags($content['ticket_detail']['detail']),120, 5000) !!}. </span> <a data-toggle="collapse" data-target="#viewdetails3">More... &raquo;</a>@endif</p> --}}
            </blockquote>
            @if($content['ticket_detail']['url'])
            <strong>Url: <span class="text-primary">{!!$content['ticket_detail']['url']!!}</span></strong>
            @endif
          </div>
        </div>
        
      </div>
      <div class="card-footer text-muted ">
        <div class="row">
        <div class="col-md-6">
          @if(sizeof($content['ticket_detail']['attachments']) > 0)
          <a  data-toggle="collapse" href="#collapse_" role="button" aria-expanded="false" aria-controls="collapse_">
                Attachments <i class="fa fa-arrow-down"></i>
          </a>
          @endif
        </div>
        <div class="col-md-6 text-right">
          Due Date:
          @if($content['ticket_detail']['due_date'])
          {{-- Assignee: <span class="text-success">Agent</span> | --}}  <span class="text-info">{{date('F jS, Y', strtotime($content['ticket_detail']['due_date']))}}</span>
          @else
          N/A
          @endif
        </div>

        <div class="col-md-12 mt-2">
          <div class="collapse" id="collapse_">
            @if(sizeof($content['ticket_detail']['attachments']) > 0)
            @php
            $count = 1;
            @endphp
            @foreach($content['ticket_detail']['attachments'] as $attachment)
              <a href="{{$attachment}}" class="badge badge-info" title="download or View" download>attathment {{$count}}</a>
              @php
              $count++;
              @endphp
            @endforeach
            @endif
          </div>
        </div>
        </div>

       
              
        
      </div>
    </div>
    <h3>Updates</h3>
    <nav>
      <div class="nav nav-tabs" id="nav-tab" role="tablist">
        <a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true">Comments</a>
        {{-- <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="false">Attachments</a> --}}
        
      </div>
    </nav>
    <div class="tab-content" id="nav-tabContent">
      
      <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
        @if($content['ticket_detail']['comments'])
          @php
          $attachment_collapse_count=0;
          @endphp
          @foreach($content['ticket_detail']['comments'] as $comment)
          <div class="card mt-3 mb-5">
            <div class="card-header bg-white d-inline">
              @if($comment['comment_email'] == null)
              <img src="https://img.icons8.com/plasticine/2x/user.png" width="20px"> {{$comment['created_by']}}
              @else
              @php
                //$comment_user = App\User::where('email',$comment['comment_email'])->first();
              @endphp
              <img src="https://img.icons8.com/plasticine/2x/user.png" width="20px"> 
              {{-- @if($comment_user)
              {{$comment_user->name}}
              @else
              UNKOWN
              @endif --}}
              {{config('app.name')}} Support
              @endif
            </div>
            <div class="card-body">
              <blockquote class="blockquote mb-0">
                <p>{!!$comment['comment']!!}</p>
                <footer class="blockquote-footer text-right">
                  {{-- @if(date('Y-m-d h:i:s') != $comment['date'])
                  {{date('h:i A', strtotime($comment['date']))}} <cite title="Source Title"></cite>{{int()$comment['date']->diffForHumans()}}Min ago
                  @else --}}
                    {{date('F jS, Y h:i A', strtotime($comment['date']))}}
                  {{-- @endif --}}
                </footer>
              </blockquote>
            </div>

            @if(count($comment['attachment']) > 0)
            <div class="card-footer text-muted text-right">
              <a  data-toggle="collapse" href="#collapse_{{$attachment_collapse_count}}" role="button" aria-expanded="false" aria-controls="collapse_{{$attachment_collapse_count}}">
                Attachments <i class="fa fa-arrow-down"></i>
              </a>
              <div class="collapse" id="collapse_{{$attachment_collapse_count}}">
                @if(sizeof($comment['attachment']) > 0)
                @php
                $count = 1;
                @endphp
                @foreach($comment['attachment'] as $attachment)
                  <a href="{{$attachment}}" class="badge badge-info" title="download or View" download>attathment {{$count}}</a>
                  @php
                  $count++;
                  @endphp
                @endforeach
                @endif
              </div>
            </div>
            @else
            @php
            // dd($comment['attachment']);
            @endphp
            @endif
          </div>
          @php
          // dd(count($comment['attachment']));
          $attachment_collapse_count++;
          @endphp
          @endforeach
          @else
          <h3 class="text-center mt-3">
            No Comment Yet
          </h3>
          
          @endif
        </div>
        <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">yjh</div>
        <div class="tab-pane fade" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab">yjh</div>
      </div>
    
    
  </div>
  <div class="col-4">
    <div class="card mb-3">
      <div class="card-header">
        Contact Information
      </div>
      <div class="card-body">
        <blockquote class="blockquote mb-3">
          Company Name
          <footer class="blockquote-footer"> SupplyChain<cite title="Source Title"> Ticketing</cite></footer>
        </blockquote>
        <blockquote class="blockquote mb-3">
          Company Email
          <footer class="blockquote-footer"> support@<cite title="Source Title">pkteam</cite>.com</footer>
        </blockquote>
        <blockquote class="blockquote mb-3">
          Website
          <footer class="blockquote-footer"> support.<cite title="Source Title">d11u</cite>.com</footer>
        </blockquote>
      </div>
    </div>
    <div class="card mb-3">
      <div class="card-header">
        Ticket Information
      </div>
      <div class="card-body">
        <blockquote class="blockquote mb-3">
          Raise By
          <footer class="blockquote-footer"><i class="fa fa-user"></i> 
            @if($content['ticket_detail']['notify_mail'])
            @php
            $user =App\User::where('email',$content['ticket_detail']['notify_mail'])->first();
            @endphp
            @if($user)
            {{$user->name}}
            @else
            UNKOWN
            @endif
            @endif
          </footer>
          @if($content['ticket_detail']['notify_mail'])
          <footer class="blockquote-footer"><i class="fa fa-envelope"></i> 
            {{$content['ticket_detail']['notify_mail']}}
          </footer>
          @endif
          @if($content['ticket_detail']['notify_mail'])
          @php
          $user =App\User::where('email',$content['ticket_detail']['notify_mail'])->first();
          @endphp
          <footer class="blockquote-footer"><i class="fa fa-users"></i>
            @if($user)
            {{$user->roles->name}}
            @else
            UNKOWN
            @endif
          </footer>
          @endif
        </blockquote>
        <blockquote class="blockquote mb-3">
          Department
          <footer class="blockquote-footer"> {{$content['ticket_detail']['department']}}</footer>
        </blockquote>
        <blockquote class="blockquote mb-3">
          Status
          <footer class="blockquote-footer">
          @if($content['ticket_detail']['status'] == 'pending')
            <label class="badge badge-warning">Pending</label>
            @endif
            @if($content['ticket_detail']['status'] == 'in_progress')
            <label class="badge badge-info">In Progress</label>
            @endif
            @if($content['ticket_detail']['status'] == 'completed')
            <label class="badge badge-success">Completed</label>
            @endif
          </footer>
        </blockquote>
      </div>
    </div>
  </div>
</div>

</div>
<!--  Content End Here -->
<footer class="bd-footer text-muted fixed-bottom text-left row mb-3">
  <div class="col-md-6 offset-md-3 " >
    <div class="card comment-box" style="display: none">
      <div class="card-body">
        <form  id="postCommentForm" method="Post" enctype="multipart/form-data"> 
        <div class="row form-group">
        @csrf
            <div class="col-md-12 mt-3 mb-2">
              <textarea class="form-control summernote comment" name="comment"></textarea>
            </div>
            <div class="col-md-6">
              <input type="file" class="form-control-file" name="file_name[]" id="comment-attachments" multiple="multiple" accept=".doc,.docx,.png,.jpg,.jpeg">
              <small>i.e:<span class="text-info"> doc,docx,png,jpg,jpeg</span></small>
            </div>
            <input type="hidden" name="ticket_ref" value="{{$ref}}">
            <input type="hidden" name="comment_email" value="{{Auth::user()->email}}">

            <div class="col-md-6 text-right">
              <button type="reset" class="btn btn-danger btn-sm comment-box-dismiss">Dismiss</button>
              <button type="submit" class="btn btn-success btn-sm">Reply</button>
            </div>
            
          </div>
        </form>
      </div>
    </div>
    {{-- <textarea class="form-control">write a comment</textarea>
    <input type="file" class="form-control-file" name="file_name[]" multiple="multiple"> --}}
  </div>

  @if($content['ticket_detail']['notify_mail'] == Auth::user()->email || $ticket_user->parent && $ticket_user->parent->email == Auth::user()->email || Auth::user()->roles->name == 'Admin')
  <div class="col-md-3 text-center">
    <button class="btn btn-success reply-btn"><i class="fa fa-reply"></i> Reply</button>
  </div>
  @endif
</footer>
<div class="modal fade" id="further_detail_modal">
  <div class="modal-dialog modal-lg modal-dialog-centered parcelpop">
    <div class="modal-content">   
      <div class="modal-header">
        <h4 class="text-capitalize fontmed">Ticket Detail</h4>
        <button type="button" class="close" data-dismiss="modal">×</button>
      </div> 
      <div class="modal-body ">
        {!!$content['ticket_detail']['detail']!!}
        @if($content['ticket_detail']['url'])
        <strong>Url: <span class="text-primary">{!!$content['ticket_detail']['url']!!}</span></strong>
        @endif
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
    $('.table-ticket').DataTable({});
    $(document).on('click', '.reply-btn', function(){
      // alert('dfd');
      $('.comment-box').show(500);
      $('.reply-btn').hide(500);

    });
    $(document).on('click', '.comment-box-dismiss', function(){
      // alert('dfd');
      $('.comment-box').hide(500);
      $('.reply-btn').show();

    });
  });
  $(document).on('submit', '#postCommentForm', function(e){
    e.preventDefault();
    var comment = $('.comment').val();
    var x = document.getElementById("comment-attachments");
    // alert($('.comment').text());
    var files = x.files;
    var check_file_type =true;
    $.each(files, function(i, file){
      // ext.push(file.name.split('.').pop().toLowerCase());
      if($.inArray(file.name.split('.').pop().toLowerCase(), ['doc','docx','png','jpg','jpeg']) == -1)
      {
        check_file_type =false;
      }
    });
    if (check_file_type == true && comment != '') 
    {
      var token = "{{config('services.ticket.api_key')}}";
      var formData = new FormData($(this)[0]);
      var headers= {
        "Authorization": "Bearer " + token,
        "Accept"       : "application/json",
      }
      proxyurl = "https://cors-anywhere.herokuapp.com/";
      url ="https://support.d11u.com/api/new-comment";
      $.ajax({
        dataType: 'json',
        method:"post",
        headers: {
          "Authorization": "Bearer " + token,
          "Accept"       : "application/json",
        },
        data:formData,
        cache: false,
        contentType: false,
        processData: false,
        url:proxyurl+url,
        beforeSend:function(){
          $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").modal('show');
       },
       success:function(data){
         console.log(data);
         // alert(data);
          $('#postCommentForm').hide();
          if(data.success == true){
            $("#loader_modal").modal('hide');

            toastr.success('Success!', 'Replyed!' ,{"positionClass": "toast-bottom-right"});
            setTimeout(function(){

              window.location.reload();
            }, 2000);
          }
        }
      });
    }
    else
    {
      if (comment == '') 
      {
        toastr.error('Error!', "Please fill comment detail section",{"positionClass": "toast-bottom-right"});
      }
      if (check_file_type == false) 
      {
        toastr.warning('Warning!', "Please select valid file type",{"positionClass": "toast-bottom-right"});
      }
      
    }
    
  });
  $(document).on('click', '.further_detail', function(){
      $('#further_detail_modal').modal('show');
  });

  @if(Session::has('successmsg'))
      toastr.success('Success!', "{{ Session::get('successmsg') }}",{"positionClass": "toast-bottom-right"});  
      @php 
       Session()->forget('successmsg');     
      @endphp  
  @endif
</script>
@stop

