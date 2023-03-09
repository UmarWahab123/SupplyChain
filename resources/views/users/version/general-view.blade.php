@extends('backend.layouts.layout')
@section('title','Versions')

@section('content')


<div class="row align-items-center mb-3">
    <div class="col-md-12 title-col">
      <h4 class="maintitle">Version Detail</h4>
    </div>    
    {{-- <div class="col-md-2 text-right">
        <a href="{{route('view-version')}}" class="btn button-st">Back</a>
    </div> --}}
</div>
  

<div class="row entriestable-row mt-2">
  <div class="col-12">
    <div class="bg-white custompadding customborder">
        <div class="row custom_loaded">
            <div class="col-lg-9">
                <h3> Change Version </h3>
            </div>
            <div class="col-lg-3">
                <select class="font-weight-bold form-control-lg form-control js-states state-tags version_numbers" name="version_numbers">
                    {{-- <option value="" selected="">Choose Primary {{$global_terminologies['category']}}</option> --}}
                    @foreach($versions as $version)
                        <option value="{{$version->id}}" > {{ $version->version}} </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-12">
                <hr/>
            </div>
        </div>
        <div class="row dynamic_data">
            <div class="col-lg-12">
                <h4>[ {{$version_detail->version}} ] {{$version_detail->title}}</h4>
            </div>
            <div class="col-lg-12">
                <hr/>
            </div>
            <div class="col-lg-12">
                <h4>Features</h4>
                <p>
                    {!! $version_detail->feature !!}
                </p>
                <h4>Bug Fix</h4>
                <p>
                    {!! $version_detail->bug_fix !!}
                </p>
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
        $(".state-tags").select2();
        
    });
    $(document).on('change', '.version_numbers', function(e){ 
        var id = $( ".version_numbers option:selected" ).val();
        $.ajax({
            method:"get",
            dataType:"json",
            data:{id:id},
            url:"{{ route('show-version-detail') }}",
            beforeSend:function(){
            $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
            });
            $("#loader_modal").modal('show');
            },
            success:function(data){
                $("#loader_modal").modal('hide');
                $('.dynamic_data').empty();
                $(".dynamic_data").append(data.html);
                // if(data.error == false){
                // toastr.success('Success!', data.successmsg ,{"positionClass": "toast-bottom-right"});
                // $('.table-versions').DataTable().ajax.reload();              
                // }
            }
        });
    });
</script>
@endsection
