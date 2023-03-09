@extends('backend.layouts.layout')
@section('title','View Version')


@section('content')


<div class="row align-items-center mb-3">
    <div class="col-md-10 title-col">
      <h4 class="maintitle">View Version</h4>
    </div>    
    <div class="col-md-2 text-right">
        <a href="{{route('view-version')}}" class="btn button-st">Back</a>
    </div>
</div>
  

<div class="row entriestable-row mt-2">
  {{-- <div class="col-12">
    <div class="bg-white custompadding customborder">
        
    </div>
  </div> --}}
  <div class="col-lg-8">
    <div class="bg-white pt-3 pl-2 h-100">
      <table id="example" class="table table-responsive headings-color sales-customer-table dataTable const-font" style="width: 100%;">
        <tbody>
          <tr>
            <td class="fontbold" style="font-size: 15px;">Version <b style="color: red;">*</b></td>
            <td style="font-size: 15px;"> 
              {{@$version->version}}
            </td>
          </tr>
          <tr>
            <td class="fontbold" style="font-size: 15px;">Title<b style="color: red;">*</b></td>
            <td style="font-size: 15px;">
              {{@$version->title}}
            </td>
          </tr>
          <tr>
            <td class="fontbold" style="font-size: 15px;">Features</td>
            <td style="font-size: 15px;">
                <p>{!!@$version->feature !!}</p>
            </td>
          </tr>
          <tr>
            <td class="fontbold" style="font-size: 15px;">Bugs Fix</td>
            <td style="font-size: 15px;">
              <p>{!! @$version->bug_fix !!}</p>
            </td>
          </tr>
          <tr class="d-none">
            <td class="fontbold" style="font-size: 15px;">Status</td>
            <td class="text-nowrap" style="font-size: 15px;">
              @if(@$item->is_publish != 0) 
                <span  class="badge badge-success"> Published</span>
              @elseif(@$configuration->email_notification == 0)
                <span  class="badge badge-info ">Not Published</span>
              @endif
            </td>
          </tr>
  
        </tbody>
      </table>
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
            $('#add-version-form').submit();
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
