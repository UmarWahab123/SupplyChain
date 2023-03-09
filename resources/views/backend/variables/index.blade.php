@extends('sales.layouts.layout')

@section('title','Terminologies')

@section('content')
    <style type="text/css">
        .invalid-feedback {
            font-size: 100%;
        }
        .disabled:disabled{
            opacity:0.5;
            cursor: not-allowed;
        }
        table.dataTable thead .sorting { background: url('../public/sort/sort_both.png') no-repeat center right;
            background-size: 5vh;}
        table.dataTable thead .sorting_asc { background: url('../public/sort/sort_asc.png') no-repeat center right;
            background-size: 5vh; }
        table.dataTable thead .sorting_desc { background: url('../public/sort/sort_desc.png') no-repeat center right;
            background-size: 5vh;}

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
          <li class="breadcrumb-item active">Terminologies List</li>
      </ol>
  </div>
</div>

    {{-- Content Start from here --}}

    <!-- header starts -->
    <div class="row d-flex align-items-center left-right-padding mb-2 form-row">
        <div class=" col-lg-6 col-md-4">
            <h3 class="maintitle text-uppercase fontbold">Terminologies List</h3>
        </div>

        <div class="col-xl-2 col-lg-3 col-md-4 ml-lg-auto ml-md-auto" >
        </div>

        <div class="col-xl-2 col-lg-3 col-md-4">
            {{-- <button class="btn recived-button text-nowrap"  data-toggle="modal" data-target="#addTermModal" id="adding-submenu">Add New Term</button> --}}
        </div> 
        <div class="col-lg-4 mt-4">
            <select class="font-weight-bold form-control-lg form-control section_filter" name="section_filter" >
                <option value="" disabled="true" selected="true">Choose Section</option>
                @foreach ($sections as $section)
                <option value="{{$section->section}}">{{$section->section}}</option>
                @endforeach
                {{-- <option value="Product Information">Product Information</option>
                <option value="Vendor Specific Information">Vendor Specific Information</option>
                <option value="Customer detail">Customer Details</option> --}}
                {{-- <option value="Order Statuses">Order Statuses</option> --}}
                {{-- <option value="Supplier Details">Supplier Details</option>
                <option value="Purchasing">Purchasing</option> --}}
                {{-- <option value="Transfer Statuses">Transfer Statuses</option> --}}
                {{-- <option value="Sale Dashboard">Sale Dashboard</option>
                <option value="Customer list">Customer list</option>
                <option value="Order Details">Order Details</option>
                <option value="Accounting">Accounting</option> --}}
                {{-- <option value="PO Statuses">PO Statuses</option>
                <option value="Accounting statuses">Accounting statuses</option> --}}
            </select>
        </div>
        <div class="col-lg-4" >
        </div>
        
        <div class="col-lg-4 mt-4 text-right">
            @if(Auth::user()->role_id == 8 || Auth::user()->parent_id != null)
                <a href="#" class="btn recived-button btn-wd add-term-btn" data-toggle="modal" data-target="#addTermModal">ADD Terminology</a>
            @endif
            <button type="button" class="btn recived-button btn-wd reset-btn">Reset</button>
        </div>
    </div>

    {{--Error msgs div--}}
    <div class="row errormsgDiv mt-2" style="display: none;">
        <div class="container" style="max-width: 100% !important; min-width: 100% !important">
            <div class="alert alert-danger alert-dismissible">
                <a href="javascript:void(0)" class="closeErrorDiv">&times;</a>
                <span id="errormsg"></span>
            </div>
        </div>
    </div>

    <!-- header ends -->
    <div class="row entriestable-row">
        <div class="col-12">
            <div class="entriesbg bg-white custompadding customborder">
                <div class="table-responsive">
                    <table class="table entriestable table-bordered table-variables text-center">
                        <thead>
                        <tr>
                            <th width="15%">Action</th>
                            <th>Section</th>
                            <th>Standard Name</th>
                            <th>Terminologies</th>
                            {{-- <th>Slug</th> --}}
                        </tr>
                        </thead>

                    </table>
                </div>
            </div>
        </div>
    </div>

    <!--  Content End Here -->

    <!-- Modal For Note -->

    <!-- Loader Modal -->
    <div class="modal" id="loader_modal" role="dialog">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-body">
                    <h3 style="text-align:center;">Please wait</h3>
                    <p style="text-align:center;"><img src="{{ asset('public/uploads/gif/waiting.gif') }}"></p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addTermModal">
        <div class="modal-dialog modal-dialog-centered parcelpop">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <div class="modal-body text-center">
                    <h3 class="text-capitalize fontmed">Add New Term</h3>
                    <div class="mt-5">
                        <form method="POST" class="add-term-form">
                            <div class="form-group">
                                <input type="text" class="font-weight-bold form-control-lg form-control" name="slug" placeholder="Slug">
                            </div>
                            <div class="form-group">
                                <select class="font-weight-bold form-control-lg form-control" name="section" >
                                    <option value="" disabled="true" selected="true">Choose Section</option>
                                    @foreach ($sections as $section)
                                    <option value="{{$section->section}}">{{$section->section}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <input type="text" name="standard_name" class="font-weight-bold form-control-lg form-control" placeholder="Standard Name">
                            </div>
                            <div class="form-group">
                                <input type="text" name="terminology" class="font-weight-bold form-control-lg form-control" placeholder="Terminology">
                            </div>
                            <div class="form-submit">
                                <input type="submit" value="add" class="btn btn-bg save-btn">
                                <input type="reset" value="close" class="btn btn-danger close-btn">
                            </div>
                        </form>
                        {{-- {!! Form::open(['method' => 'POST', 'class' => 'add-term-form']) !!}
                        <div class="form-group">
                            {!! Form::text('slug', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Slug']) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::select('section', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Section']) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::text('standard_name', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Standard Name']) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::text('terminology', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Terminology']) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::text('page', $value = null, ['class' => 'font-weight-bold form-control-lg hidden form-control', 'placeholder' => 'Page']) !!}
                        </div>

                        <div class="form-submit">
                            <input type="submit" value="add" class="btn btn-bg save-btn">
                            <input type="reset" value="close" class="btn btn-danger close-btn">
                        </div>
                        {!! Form::close() !!} --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editTermModal">
        <div class="modal-dialog modal-dialog-centered parcelpop">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <div class="modal-body text-center">
                    <h3 class="text-capitalize fontmed">Edit Term</h3>
                    <div class="mt-5">
                        {!! Form::open(['method' => 'POST', 'class' => 'edit-term-form']) !!}
                            {{ csrf_field() }}

                            {{-- <div class="form-group mb-4 pb-1 d-none">
                                <input type="text" name="slug" disabled class="font-weight-bold form-control-lg form-control term-slug" placeholder="Slug" required="true">
                            </div> --}}

                            {{-- <div class="form-group mb-4 pb-1 d-none">
                                <label for="section" style="float: left;">Section</label>
                                <input required type="text" name="section" class="font-weight-bold form-control-lg form-control term-section" placeholder="Section">
                            </div> --}}

                            {{-- <div class="form-group mb-4 pb-1 d-none">
                                <label for="standard_name" style="float: left;">Standard Name</label>
                                <input required type="text" name="standard_name" class="font-weight-bold form-control-lg form-control term-standard_name" placeholder="Standard Name">
                            </div> --}}

                            <div class="form-group mb-4 pb-1">
                                <label for="terminology" style="float: left;">Terminology</label>
                                <input required type="text" name="terminology" class="font-weight-bold form-control-lg form-control term-value" placeholder="System Name">
                            </div>

                            <div class="form-group mb-4 pb-1">
                                <input type="text" name="page" class="font-weight-bold form-control-lg hidden form-control term-page" placeholder="Page">
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

@endsection

@section('javascript')
    <script type="text/javascript">
        $(function(e){
            $('.table-variables').DataTable({
                "sPaginationType": "listbox",
                processing: false,
                "language": {
                    processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
                ordering: false,
                serverSide: true,
                pageLength:100,

                // ajax: "{!! url('admin/get-variables') !!}",
                ajax: 
                    {
                    beforeSend: function(){
                        $('#loader_modal').modal('show');
                    },
                    url: "{!! route('get-variables') !!}",
                    data: function(data) { data.section_filter = $('.section_filter option:selected').val()} ,
                    },
                columns: [
                    { data: 'action', name: 'action' },
                    {data:'section', name:'section'},
                    { data: 'page', name: 'page' },
                    { data: 'terminology', name: 'terminology' },
                    // { data: 'slug', name: 'slug' },
                ],
                drawCallback: function(){
                $('#loader_modal').modal('hide');
                }

            });

            
    $('.dataTables_filter input').unbind();
    $('.dataTables_filter input').bind('keyup focusout', function(e) {
      let searchSession;
      let searchField;
      let count;
     searchField=$(this).val();
     searchField=searchField.trim();
     $('#tableSearchField').val(searchField);
     count=searchField.length;
      if(e.keyCode == 13) {
      
         $('.table-variables').DataTable().search($(this).val()).draw();
        return;
      }else if(count>0){
        if(e.type == 'focusout'){
           $('.table-variables').DataTable().search(this.value).draw();
              return;
                   }
        }else if( searchField==""){
                 $('input[type=search]').empty();
                 return;
        }
 });
            $(document).on('change','.section_filter',function(){
                var selected = $('.section_filter option:selected').val();
                if($('.section_filter option:selected').val() != '')
                {
                $('#loader_modal').modal({
                    backdrop: 'static',
                    keyboard: false
                    });
                $("#loader_modal").modal('show');
                $('.table-variables').DataTable().ajax.reload();
                }
            });

            $('.reset-btn').on('click',function(){
                $('.section_filter').val('').change();
                $('#loader_modal').modal({
                    backdrop: 'static',
                    keyboard: false
                    });
                $("#loader_modal").modal('show');
                $('.table-variables').DataTable().ajax.reload(); 
            });

            $(document).on('submit', '.add-term-form', function(e){
                e.preventDefault();
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    }
                });
                $.ajax({
                    url: "{{ route('add-variable') }}",
                    method: 'post',
                    data: $('.add-term-form').serialize(),
                    beforeSend: function(){
                        $('.add-term-form .save-btn').val('Please wait...');
                        $('.add-term-form .save-btn').addClass('disabled');
                        $('.add-term-form .save-btn').attr('disabled', true);
                    },
                    success: function(result){
                        $('.add-term-form .save-btn').val('add');
                        $('.add-term-form .save-btn').attr('disabled', true);
                        $('.add-term-form .save-btn').removeAttr('disabled');
                        if(result.success === true){
                            $('.modal').modal('hide');
                            toastr.success('Success!', 'Term added successfully!',{"positionClass": "toast-bottom-right"});
                            $('.add-term-form')[0].reset();
                            setTimeout(function(){
                                $('.table-variables').DataTable().ajax.reload();
                            }, 2000);
                        }


                    },
                    error: function (request, status, error) {
                        $('.add-term-form .save-btn').val('add');
                        $('.add-term-form .save-btn').removeClass('disabled');
                        $('.add-term-form .save-btn').removeAttr('disabled');
                        $('.add-term-form .form-control').removeClass('is-invalid');
                        $('.add-term-form .form-control').next().remove();
                        json = $.parseJSON(request.responseText);
                        $.each(json.errors, function(key, value){
                            $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                            $('input[name="'+key+'"]').addClass('is-invalid');
                        });
                    }
                });
            });

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
                    // $x.next().next('span').removeClass('d-none');
                    // $x.next().next('span').addClass('active');

                }, 300);
            });

            $(document).on('keypress keyup focusout', '.fieldFocus', function(e){

                if (e.keyCode === 27 && $(this).hasClass('active')) {
                var fieldvalue = $(this).prev().data('fieldvalue');
                var thisPointer = $(this);
                    thisPointer.addClass('d-none');
                    thisPointer.val(fieldvalue);
                    thisPointer.removeClass('active');
                    thisPointer.prev().removeClass('d-none');
                }

                var fieldvalue = $(this).prev().data('fieldvalue');
                var new_value = $(this).val();

                if( (e.keyCode === 13 || e.which === 0) && $(this).hasClass('active')){

                var old_value = $(this).prev().data('fieldvalue');
                var tId = $(this).parents('tr').attr('id');

                if(fieldvalue == new_value)
                {
                var thisPointer = $(this);
                thisPointer.addClass('d-none');
                thisPointer.removeClass('active');
                thisPointer.prev().removeClass('d-none');
                }

                else if($(this).val() !== '' && $(this).hasClass('active'))
                {
                var new_value = $(this).val();
                $(this).prev().removeData('fieldvalue');
                $(this).prev().data('fieldvalue', new_value);
                $(this).attr('value', new_value);

                $(this).removeClass('active');
                $(this).addClass('d-none');
                $(this).prev().removeClass('d-none');
                $(this).prev().html(new_value);
                $(this).prev().css("color", "");
                saveProdData(tId, $(this).attr('name'), $(this).val(), old_value);
                }
                }
            });

            function saveProdData(t_id,field_name,field_value,old_value){
                // console.log(field_name+' '+field_value+''+t_id+' '+old_value);

                // return false;
                $.ajaxSetup({
                    headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    }
                });
                $.ajax({
                    method: "post",
                    url: "{{ route('edit-variable') }}",
                    dataType: 'json',
                    data: 't_id='+t_id+'&'+field_name+'='+field_value+'&'+'old_value'+'='+old_value,
                    beforeSend: function(){
                    $('#loader_modal').modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                    $("#loader_modal").modal('show');
                    },
                    success: function(data)
                    {
                    $("#loader_modal").modal('hide');
                    if(data.success == true)
                    {
                        toastr.success('Success!', 'Terminologies updated successfully.',{"positionClass": "toast-bottom-right"});
                    }
                    },

                });
            }


            $(document).on('submit', '.edit-term-form', function(e){
                e.preventDefault();
                var form = $(this);
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    }
                });
                $.ajax({
                    url: "{{ route('edit-variable') }}",
                    method: 'post',
                    data: $('.edit-term-form').serialize(),
                    beforeSend: function(){
                        $('.edit-term-form .save-btn').val('Please wait...');
                        $('.edit-term-form .save-btn').addClass('disabled');
                        $('.edit-term-form .save-btn').attr('disabled', true);
                    },
                    success: function(result){
                        $('.edit-term-form .save-btn').val('Update');
                        $('.edit-term-form .save-btn').attr('disabled', false);
                        $('.edit-term-form .save-btn').removeClass('disabled');
                        if(result.success === true){
                            $('.modal').modal('hide');
                            toastr.success('Success!', 'Term updated successfully!',{"positionClass": "toast-bottom-right"});
                            $('.edit-term-form')[0].reset();
                            setTimeout(function(){
                                $('.table-variables').DataTable().ajax.reload();
                            }, 2000);
                        }


                    },
                    error: function (request, status, error) {
                        $('.edit-term-form .save-btn').val('add');
                        $('.edit-term-form .save-btn').removeClass('disabled');
                        $('.edit-term-form .save-btn').removeAttr('disabled');
                        $('.edit-term-form .form-control').removeClass('is-invalid');
                        $('.edit-term-form .form-control').next().remove();
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
                // var oldSection = $(this).parents('td').next().text();
                var oldSlug = $(this).parents('td').next().next().next().text();
                // var oldStd_name = $(this).parents('td').next().next().next().next().text();
                //var oldTerminology = $(this).parents('td').next().text();
               // var oldPage = $(this).parents('td').next().next().next().text();

                //$('.edit-term-form .term-slug').val(oldSlug);
                $('.edit-term-form .term-value').val(oldSlug);
                //$('.edit-term-form .term-page').val(oldPage);
                $('input[name=editid]').val(sId);
                // $('.edit-term-form .term-section').val(oldSection);
                // $('.edit-term-form .term-standard_name').val(oldStd_name);
                $('#editTermModal').modal('show');
            });

            $(document).on('click', '.delete-variable', function(){
                var id = $(this).data('id');
                var variable_name = $(this).data('variable_name');
                swal({
                        title: "Alert!",
                        text: "Are you sure you want to delete term  ["+ variable_name +"] ?",
                        type: "info",
                        showCancelButton: true,
                        confirmButtonClass: "btn-danger",
                        confirmButtonText: "Yes!",
                        cancelButtonText: "No!",
                        closeOnConfirm: true,
                        closeOnCancel: false
                    },
                    function(isConfirm) {
                        if (isConfirm){
                            $.ajaxSetup({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                                }
                            });
                            $.ajax({
                                method:"post",
                                dataType:"json",
                                data:{id:id},
                                url:"{{ route('delete-variable') }}",
                                beforeSend:function(){
                                    $('#loader_modal').modal({
                                        backdrop: 'static',
                                        keyboard: false
                                    });
                                    $("#loader_modal").modal('show');
                                },
                                success:function(result){
                                    $("#loader_modal").modal('hide');
                                    if(result.success === true){
                                        $('.modal').modal('hide');
                                        toastr.success('Success!', 'Term Deleted successfully!',{"positionClass": "toast-bottom-right"});
                                        setTimeout(function(){
                                            $('.table-variables').DataTable().ajax.reload();
                                        }, 2000);
                                    }
                                }
                            });
                        }
                        else{
                            swal("Cancelled", "", "error");
                        }
                    });
            });
        });
    </script>
@stop
