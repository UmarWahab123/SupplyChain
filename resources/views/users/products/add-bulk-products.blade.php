@extends('users.layouts.layout')

@section('title','Products Management | Supply Chain')

@section('content')


<div class="row mb-3">
  <div class="col-md-8 title-col">
    <h3 class="maintitle">Add Bulk Products</h3>
  </div>
</div>


<div class="row mb-3 justify-content-center ">
  <div class="col-lg-12 col-md-12 col-12 signform-col">
    <div class="row add-gemstone">
      <div class="col-md-12">
        <div class="bg-white pr-4 pl-4 pt-4 pb-5">

          <ul class="nav nav-tabs">
            <li class="nav-item">
              <a class="nav-link cut-tab active" data-toggle="tab" href="#tab1">Add Bulk Products</a>
            </li>
          </ul>

          <div class="tab-content mt-3">
            <div class="tab-pane active" id="tab1">                              
              <div class="form-group">
                <button class="btn btn-info pull-right" id="alreadybtn" >Already Have File</button>
                <a href="{{asset('public/site/assets/purchasing/product_excel/Bulk_Products.xlsx')}}" download><span class="btn btn-success pull-right  mr-1" id="examplefilebtn">Download Example File</span></a>
              </div>
                    
              <br>
              <div id="uploadForm" style="display: none;">
                <h3>Upload File</h3>
                <label><strong>Note : </strong>Please use the downloaded file for upload only.</label>
                <form action="{{url('upload-bulk-product')}}" class="upload-excel-form" method="post" enctype="multipart/form-data">
                  {{csrf_field()}}                  
                  <label for="bulk_import_file">Choose Excel File</label>
                  <input type="file" class="form-control" name="excel" id="excel" accept=".xls,.xlsx" required=""><br>
                  <button class="btn btn-info products-upload-btn" type="submit">Upload</button>
                </form>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>  
  </div> 
</div> 

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

{{-- Content End Here  --}}

@endsection

@section('javascript')

<script type="text/javascript">
$(function(e){  
  $(document).on('click','.products-upload-btn',function(){
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $("#loader_modal").modal('show');
  });

  $(document).on('click','#alreadybtn , #examplefilebtn',function(){
    $('#uploadForm').show(300);
  });

});
</script>
@endsection