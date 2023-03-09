@extends('users.layouts.layout')

@section('title','Customers Management | Supply Chain')

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

{{-- Content Start from here --}}
<div class="row mb-0">
  <div class="col-md-10 title-col">
    <h3 class="maintitle text-uppercase fontbold mb-0 mt-1">Customers CENTER</h3>
  </div>
  
  
</div>


<div class="row entriestable-row mt-3">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">


    
    @include('includes.customer_datatable.html');
      

    </div>
    
  </div>
</div>


</div>
<!--  Content End Here -->





<!-- Loader Modal -->
<div class="modal" id="loader_modal_old" role="dialog">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-body">
        <h3 style="text-align:center;">Please wait</h3>
        <p style="text-align:center;"><img src="{{ asset('public/uploads/gif/waiting.gif') }}"></p>
      </div>
    </div>
  </div>
</div>



@endsection

@section('javascript')
  <script src="{{asset('assets/js/customers_list/customers_list.js')}}"></script>

@stop

