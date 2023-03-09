@extends('backend.layouts.layout')
@section('title','View Roles')


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
      </ol>
  </div>
</div>

{{-- Content Start from here --}}
{{-- <div class="row mb-0">
  <div class="col-md-12 title-col">
    <div class="d-sm-flex justify-content-between">
      <h4 class="text-uppercase fontbold">Roles</h4> 
        <div class="mb-0">
        <a href="#" class="btn button-st d-none" data-toggle="modal" data-target="#addrolemodal">Add new</a>
        </div>
    </div>
  </div>
</div> --}}


<div class="row align-items-center mb-3">
  <div class="col-md-8 title-col">
      <h4 class="maintitle">Roles</h4>
  </div>    
      <div class="col-md-4 text-right">
         <a href="#" class="btn button-st d-none" data-toggle="modal" data-target="#addrolemodal">Add new</a>
  </div>
</div>
  

<div class="row entriestable-row mt-2">
  <div class="col-12">
    <div class="bg-white custompadding customborder">

      
      <div class="gemstonetable"> 
       {{--  <div class="mb-4 gemstonelqty">
        <input type="number" name="gemsqty" value="01" class="form-control">
        </div> --}}
      
      <div class="table-responsive">
          <table class="table entriestable table-bordered">
              <thead class="text-center">
                <tr>
                  <th>#</th>
                  <th>Role</th>   
                </tr>
              </thead>
              <tbody class="text-center">
                
                @php
                 $get_counter =1;
                @endphp

                @foreach($roles as $result)
                <tr>
                  <td>{{$get_counter}}</td>
                  <td> <a href="{{ route('role-menus', ['role_id' => $result->id]) }}"><b>{{$result->name}}</b></a> </td>
                 
                </tr> 
                 
                 @php
                  $get_counter++;
                 @endphp
                @endforeach
               
              </tbody>
              
          </table>

        </div>
        
       {{--  <div class="d-sm-flex justify-content-between align-items-center">
          <div class="showentries">Show 1 to 13 of 50 entries</div>
          <div class="d-flex d-md-block table-responsive justify-content-center  mt-3 mt-sm-0 paginationcol">
            <ul class="pagination  mb-0">
            <li class="page-item"><a class="page-link" href="#">Previous</a></li>
            <li class="page-item active"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item"><a class="page-link" href="#">Next</a></li>
            </ul> 
          </div>
        </div>
 --}}
        </div> 

        </div>
  </div>
</div>


<!--  Content End Here -->

<!--  Roles Adding Modal Start Here -->
<div class="modal fade" id="addrolemodal">
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
  </div>


@endsection

@section('javascript')
  <script type="text/javascript">
    $(function(e){

      $(document).on('click', '.save-btn', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('add-role') }}",
          method: 'post',
          data: $('.add-role-form').serialize(),
          beforeSend: function(){
            $('.save-btn').val('Please wait...');
            $('.save-btn').addClass('disabled');
            $('.save-btn').attr('disabled', true);
          },
          success: function(result){
            $('.save-btn').val('add');
            $('.save-btn').attr('disabled', true);
            $('.save-btn').removeAttr('disabled');
            if(result.success === true){
              $('.modal').modal('hide');
              toastr.success('Success!', 'Role added successfully',{"positionClass": "toast-bottom-right"});
              $('.add-role-form')[0].reset();
              setTimeout(function(){
                window.location.reload();
              }, 2000);
              
            }
            
            
          },
          error: function (request, status, error) {
                $('.save-btn').val('add');
                $('.save-btn').removeClass('disabled');
                $('.save-btn').removeAttr('disabled');
                $('.form-control').removeClass('is-invalid');
                $('.form-control').next().remove();
                json = $.parseJSON(request.responseText);
                $.each(json.errors, function(key, value){
                    $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                     $('input[name="'+key+'"]').addClass('is-invalid');
                });
            }
        });
    });

    });
  </script>
@endsection
