@extends('backend.layouts.layout')
@section('title','Edit Version')


@section('content')

<div class="row align-items-center mb-3">
    <div class="col-md-10 title-col">
      <h4 class="maintitle">Edit Version</h4>
    </div>    
    <div class="col-md-2 text-right">
        <a href="{{route('view-version')}}" class="btn button-st">Back</a>
    </div>
</div>
  

<div class="row entriestable-row mt-2">
  <div class="col-12">
    <div class="bg-white custompadding customborder">
        <form action="{{ route('update-version')}}" method="POST" id="edit-version-form" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" value="{{$version->id}}">
            <div class="row">
                <div class="col-md-6">
                    <label > Version </label>
                    <input type="text" name="version" id="version" class="form-control"  placeholder="Enter Version" value="{{$version->version}}">
                    <div class="alert alert-warning mt-1 alert-title-version d-none" role="alert">
                        Kindly Enter Version!
                    </div>
                </div>
                <div class="col-md-6">
                    <label > Title </label>
                    <input type="text" name="title" id="title" class="form-control"  placeholder="Enter Version Title" value="{{$version->title}}">
                    <div class="alert alert-warning mt-1 alert-title-title d-none" role="alert">
                        Kindly Enter Title!
                    </div>
                </div>
                <div class="form-group col-12">
                    <label for="inputEmail3" class=" control-label">Features</label>
                    <textarea type="text" name="features" class="font-weight-bold form-control-lg form-control summernote ticket-description" id="version-features"  placeholder="Description">{{$version->feature}}</textarea>
                </div>
                <div class="form-group col-12">
                    <label for="inputEmail3" class=" control-label">Bugfixes</label>
                    <textarea type="text" name="bugfixes" class="font-weight-bold form-control-lg form-control summernote ticket-description" id="version-bugfix" placeholder="Description (Required)">{{$version->bugfix}}</textarea>
                </div>
                <div class="form-group col-12">
                    <button type="submit" class="btn btn-info float-right mt-3 submit-btn">Update</button>
                </div>
            </div>
        </form>
    </div>
  </div>
</div>


<!--  Content End Here -->

@endsection

@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {
        $('.summernote').summernote();
    });
    $(document).on('click', '.submit-btn', function(e){ 
        e.preventDefault();
        submit =true;
        // $('#select_role').val();
        if (!$('#title').val()) 
        {
            toastr.error('Warning!',"Title can't be null",{" positionClass": "toast-bottom-right"});  
            submit = false;
            // alert('check');
        }
        if (!$('#version').val()) 
        {
            toastr.error('Warning!',"Version can't be null",{" positionClass": "toast-bottom-right"});  
            submit = false;
            // alert('check');
        }
        if (submit == true) {
            $('#edit-version-form').submit();
        }
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
    $(function(e){

    });
</script>
@endsection
