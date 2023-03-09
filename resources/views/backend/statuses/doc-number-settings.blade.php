@extends('backend.layouts.layout')

@section('title','Statuses | Supply Chain')

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
          <li class="breadcrumb-item active">Document Number Settings</li>
      </ol>
  </div>
</div>

<!-- prod-cat = Product-category -->
{{-- Content Start from here --}}
<div class="row align-items-center mb-3">
  <div class="col-md-8 title-col">
    <h3 class="maintitle">Document Number Settings</h3>
  </div>
</div>


<div class="row entriestable-row mt-3">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">

    <div class="table-responsive">
      <table class="table entriestable table-bordered table-statuses text-center">
        <thead>
          <tr>
            <th>Document Type</th>
            <th>Type Prefix</th>                    
            <th>Counter Formula</th>
            <th>Reset #</th>
            <!-- <th>Separate Counter</th> -->
          </tr>
        </thead>
      </table>
    </div>  
    
  </div>  
</div>
</div>

<!--  Content End Here -->
@endsection
@section('javascript')
<script type="text/javascript">
  $(function(e){
     $('.table-statuses').DataTable({
      "sPaginationType": "listbox",
      processing: false,
      "language": {
      processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      ordering: false,
      serverSide: true,
      columnDefs: [
      { className: "dt-body-left", "targets": [ 0,1,2,3] },
      { className: "dt-body-right", "targets": [] }
      ],
      ajax: {
        beforeSend: function(){
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
        $("#loader_modal").modal('show');
      },
      url:"{!! route('get-doc-number-settings') !!}",
      },
      columns: [
          { data: 'title', name: 'title' },
          { data: 'prefix', name: 'prefix' },
          { data: 'counter_formula', name: 'counter_formula' },
          { data: 'reset', name: 'reset' },
          // { data: 'counter', name: 'counter' },
      ],
        drawCallback: function(){
      $('#loader_modal').modal('hide');
    },
    });

    $(document).on('keyup', '.form-control', function(){
      $(this).removeClass('is-invalid');
      $(this).next().remove();
    });

     // to make fields double click editable
  $(document).on("dblclick",".inputDoubleClick",function(){
        $x = $(this);
        $(this).addClass('d-none');
        $(this).after('<span class="spinner"><i class="fa fa-spinner"></i></span>');
        
      setTimeout(function(){ 
        
        $('.spinner').remove();
        $x.next().removeClass('d-none');
        $x.next().addClass('active');
        $x.next().focus();
        var num = $x.next().val();        
        $x.next().focus().val('').val(num);
        $x.next().next('span').removeClass('d-none');
        $x.next().next('span').addClass('active');

       }, 300);      
  });

  $(document).on('keypress keyup focusout', '.fieldFocus', function(e){
   
    var id = $(this).prev().data('id');
    var fieldvalue = $(this).prev().data('fieldvalue');
    if (e.keyCode === 27 && $(this).hasClass('active')) {
      var thisPointer = $(this);
      thisPointer.addClass('d-none');
      thisPointer.val(fieldvalue);
      thisPointer.prev().removeClass('d-none');
      thisPointer.removeClass('active');
    }

    else if( (e.keyCode === 13 || e.which === 0) && $(this).hasClass('active')){
    var status_id = $(this).parents('tr').attr('id');
    var new_value = $(this).val();
   
    if($(this).val() !== '' && $(this).hasClass('active'))
    {
      if(fieldvalue == new_value)
      {
        var thisPointer = $(this);
        thisPointer.addClass('d-none');    
        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
      }
      else
      {
        $(this).removeClass('active');
        $(this).addClass('d-none');
        $(this).prev().removeClass('d-none');
        $(this).prev().html(new_value);
        $(this).prev().removeData('fieldvalue');
        $(this).prev().data('fieldvalue', new_value);
        saveChanges(status_id, $(this).attr('name'), $(this).val());
      }
      
    }     
  }
});

  function saveChanges(status_id,field_name,field_value){
      console.log(field_name+' '+field_value+''+status_id);
      // return false; 
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method: "post",
        url: "{{ route('update-doc-number-settings') }}",
        dataType: 'json',
        data: 'status_id='+status_id+'&'+field_name+'='+field_value,
        beforeSend: function(){
          // shahsky here
        },
        success: function(data)
        {
          if(data.status == true)
          {
            toastr.success('Success!', 'Information Updated Successfully' ,{"positionClass": "toast-bottom-right"});
          }       
        },

      });
    }


  });
</script>
@stop

