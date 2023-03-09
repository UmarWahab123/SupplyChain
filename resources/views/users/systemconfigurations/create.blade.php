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
.ck.ck-content.ck-editor__editable {
    height: 180px;
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
          <li class="breadcrumb-item"><a href="{{route('system-configurations')}}">System Configuration</a></li>
          <li class="breadcrumb-item active">Create</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}
<div class="row align-items-center mb-3">
  <div class="col-md-8 title-col">
    <h3 class="maintitle">SYSTEM CONFIGURATION</h3>
  </div>
</div>


<div class="row entriestable-row mt-3">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">

      <div class="d-sm-flex justify-content-between">
        <h4>Add Guideline</h4>
        <div class="mb-3">
          <a class="btn" href="{{ route('system-configurations') }}">
            Back
          </a>
        </div>
      </div>
      <div class="row d-sm-flex">
      <div class="col-sm-8">
        {!! Form::open(['url' => route("store-system-configurations")]) !!}
        <div class="form-group">
          <label for="type" class="font-weight-bold">Type <span class="text-danger">*</span></label>


          {!! Form::text('type',  $value = null, ['class' => 'form-control '.($errors->has('type') ? 'is-invalid':''), 'placeholder' => 'type', 'id' => 'type']) !!}

          @if ($errors->has('type'))
              <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('type') }}</strong>
              </span>
          @endif
        </div>
        <div class="form-group">
          <label for="subject" class="font-weight-bold">Subject <span class="text-danger">*</span></label>
          {!! Form::text('subject', $value = null, ['class' => 'form-control '.($errors->has('subject') ? 'is-invalid':''), 'placeholder' => 'Subject', 'id' => 'subject']) !!}
          @if ($errors->has('subject'))
              <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('subject') }}</strong>
              </span>
          @endif
        </div>
        <div class="form-group">
          <label for="detail" class="font-weight-bold">Detail <span class="text-danger">*</span></label>
          {!! Form::textarea('detail', $value = null, ['class' => 'form-control '.($errors->has('detail') ? 'is-invalid':''), 'placeholder' => 'Detail', 'id' => 'detail']) !!}
          @if ($errors->has('detail'))
              <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('detail') }}</strong>
              </span>
          @endif
        </div>
        <div class="form-group">
          {!! Form::submit('Save', ['class' => 'btn btn-bg']) !!}
        </div>
        {!! Form::close() !!}
      </div>
      <div class="col-sm-4 mt-4 pt-2">
        <div class="border p-3 bg-light">
          <strong>Note: </strong>Please copy and paste the following variables in the editor as they are.
          <p class="mb-1">[[name]]</p>
          <p class="mb-1">[[first_name]]</p>
          <p class="mb-1">[[last_name]]</p>
          <p class="mb-1">[[email]]</p>
          <p class="mb-1">[[password]]</p>
          <p class="mb-1">[[orderId]]</p>
          <p class="mb-1">[[invoiceId]]</p>          
          <p class="mb-1">[[Link]]</p>
        </div>
      </div>
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
    ClassicEditor
        .create( document.querySelector( '#content' ) )
        .catch( error => {
            console.error( error );
        });
    $(document).on('keyup', '.form-control', function(){
      $(this).removeClass('is-invalid');
      $(this).next().remove();
    });
  });
</script>
@stop

