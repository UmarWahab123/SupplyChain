@extends('backend.layouts.layout')

@section('title','Brands | Supply Chain')

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
<!-- prod-cat = Product-category -->
{{-- Content Start from here --}}
<div class="row align-items-center mb-3">
  <div class="col-md-8 title-col">
      <h4 class="maintitle">Brands</h4>
  </div>    
      <div class="col-md-4 text-right title-right-col">
        <a href="#" class="btn button-st" data-toggle="modal" data-target="#addBrandModal">ADD BRAND</a>
  </div>
</div>


<div class="row entriestable-row mt-2">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">

    <div class="table-responsive">
          <table class="table entriestable table-bordered table-brands text-center">
              <thead>
                  <tr>
                      <th>Action</th>
                      <th>Title</th>    
                      <th>Created At</th>
                      <th>Updated At</th>
                  </tr>
              </thead>
               
          </table>
        </div>  
        </div>
    
  </div>
</div>

</div>
<!--  Content End Here -->

<!--  Brand Modal Start Here -->
<div class="modal fade" id="addBrandModal">
    <div class="modal-dialog modal-dialog-centered parcelpop">
      <div class="modal-content">   
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div> 
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Add brand</h3>
          <div class="mt-5">
          {!! Form::open(['method' => 'POST', 'class' => 'add-brand-form']) !!}
            <div class="form-group">
              {!! Form::text('title', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Enter Title']) !!}
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

<!-- add Brand Modal End Here -->

<!-- Edit modal -->
  <div class="modal fade" id="editBrandModal">
    <div class="modal-dialog modal-dialog-centered parcelpop">
      <div class="modal-content">   
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div> 
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Edit @if(!array_key_exists('brand', $global_terminologies)) Brand @else {{$global_terminologies['brand']}} @endif</h3>
          <div class="mt-5">
          <form method="POST" action="{{route('edit-brand')}}" class="edit-brand-form">
            {{ csrf_field() }}
            <div class="form-group mb-4 pb-1">
              <input type="text" name="title" class="font-weight-bold form-control-lg form-control payment_name" placeholder="Enter Brand Title" required="true">
            </div>

            <div class="form-submit">
              <input type="hidden" name="editid">
              <input type="submit" value="Update" class="btn btn-bg save-btn">
            </div>

          </form>
         </div> 
        </div>
      </div>
    </div>
  </div>
<!-- Edit modal End -->


@endsection

@section('javascript')
<script type="text/javascript">
  $(function(e){

    $(document).on("focus", ".datepicker", function(){
        $(this).datetimepicker({
            timepicker:false,
            format:'Y-m-d'});
    });
     $('.table-brands').DataTable({
         processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        ordering: false,
        serverSide: true,
        ajax: "{!! route('get-brands') !!}",
        columns: [
            { data: 'action', name: 'action' },
            { data: 'title', name: 'title' },
            { data: 'created_at', name: 'created_at' },
            { data: 'updated_at', name: 'updated_at' }
        ]
        
    });

    $(document).on('keyup', '.form-control', function(){
      $(this).removeClass('is-invalid');
      $(this).next().remove();
    });

    

    $(document).on('submit', '.add-brand-form', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('add-brand') }}",
          method: 'post',
          data: $('.add-brand-form').serialize(),
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
              toastr.success('Success!', 'Brand added successfully',{"positionClass": "toast-bottom-right"});
              $('.add-brand-form')[0].reset();
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

    $(document).on('submit', '.edit-brand-form', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('edit-brand') }}",
          method: 'post',
          data: $('.edit-brand-form').serialize(),
          beforeSend: function(){
            $('.save-btn').val('Please wait...');
            $('.save-btn').addClass('disabled');
            $('.save-btn').attr('disabled', true);
          },
          success: function(result){
            $('.save-btn').val('add');
            $('.save-btn').attr('disabled', false);
            $('.save-btn').removeClass('disabled');
            if(result.success === true){
              $('.modal').modal('hide');
              toastr.success('Success!', 'Brand edited successfully',{"positionClass": "toast-bottom-right"});
              $('.edit-brand-form')[0].reset();
              setTimeout(function(){
                window.location.reload();
              }, 2000);
              
            }
            
            
          },
          error: function (request, status, error) {
                $('.save-btn').val('add');
                $('.save-btn').attr('disabled', false);
                $('.save-btn').removeClass('disabled');
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

    $(document).on('click', '.edit-icon',function(e){
      var sId = $(this).parents('tr').attr('id');
      var oldTitle = $(this).parents('td').next().text();
      $('.payment_name').val(oldTitle);
      $('input[name=editid]').val(sId);
      $('#editBrandModal').modal('show');
    });

  });
</script>
@stop

