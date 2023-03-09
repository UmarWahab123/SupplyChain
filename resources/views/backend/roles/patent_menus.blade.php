@extends('sales.layouts.layout')

@section('title','Menus')

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

    {{-- Content Start from here --}}

    <!-- header starts -->
    <div class="row d-flex align-items-center left-right-padding mb-2 form-row">
        <div class=" col-lg-6 col-md-4">
           
        </div>

        <div class="col-xl-2 col-lg-2 col-md-4 ml-lg-auto ml-md-auto" >
        <button class="btn recived-button text-nowrap"  data-toggle="modal" data-target="#addMenuModal" id="adding-menu">Add Parent Menu</button>

        </div>

        <!-- <div class="col-xl-2 col-lg-3 col-md-4">
            <button class="btn recived-button text-nowrap"  data-toggle="modal" data-target="#addMenuModal" id="adding-menu">Add Parent Menu</button>
            
        </div> -->
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
    <div class="row col-12 ">
        <div class="col-lg-12 col-md-12 col-12">
        <div class="mb-2">
            <h3 class="maintitle text-uppercase fontbold">parent menus</h3>
        </div>
            <div class="entriesbg bg-white custompadding customborder">
                <div class="table-responsive" style="max-height:800px">
                    <table class="table entriestable table-bordered table-menus text-center" >
                        <thead>
                        <tr>
                        <th style="width:100px">Action</th>
                            <th style="width:100px">Title</th>
                            <th style="max-width:80px">URL</th>
                            <th style="width:100px">Route</th>
                            <th style="width:100px">Icon</th>

                        </tr>
                        </thead>

                    </table>
                </div>
            </div>
        </div>

        <!-- <div class="col-lg-6 col-md-12 col-12">
        <div class="mb-2">
            <h3 class="maintitle text-uppercase fontbold">Global Access</h3>
        </div>
            <div class="entriesbg bg-white custompadding customborder">
            <div class="table-responsive" style="max-height:800px">

                    <table class="table entriestable table-bordered table-menus text-center">
                        <thead>
                        <tr>
                        <th style="width:100px">Action</th>
                            <th style="width:100px">Title</th>
                            <th style="max-width:80px">URL</th>
                            <th style="width:100px">Route</th>
                            <th style="width:100px">Icon</th>

                        </tr>
                        </thead>

                    </table>
                </div>
            </div>
        </div> -->
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

    <div class="modal fade" id="addMenuModal">
        <div class="modal-dialog modal-dialog-centered parcelpop">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <div class="modal-body text-center">
                    <h3 class="text-capitalize fontmed">Add Menu</h3>
                    <div class="mt-5">
                        {!! Form::open(['method' => 'POST','files'=>'true', 'id' => 'add-menu-form', 'url'=> route('add-menu') ]) !!}

                        <div class="form-group">
                            {!! Form::text('title', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Title']) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::text('url', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'URL']) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::text('route', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Route']) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::file('icon', ['class' => 'font-weight-bold form-control-lg form-control']); !!}
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

    <div class="modal fade" id="editMenuModal">
        <div class="modal-dialog modal-dialog-centered parcelpop">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <div class="modal-body text-center">
                    <h3 class="text-capitalize fontmed">Edit Menu</h3>
                    <div class="mt-5">
                        <form method="POST" enctype="multipart/form-data" id="edit-menu-form" action="{{route('edit-menu')}}">
                            {{ csrf_field() }}

                            <div class="form-group mb-4 pb-1">
                                <input type="text" name="title" class="font-weight-bold form-control-lg form-control menu-title" placeholder="Title" required="true">
                            </div>

                            <div class="form-group mb-4 pb-1">
                                <input type="text" name="url" class="font-weight-bold form-control-lg form-control menu-url" placeholder="URL">
                            </div>

                            <div class="form-group mb-4 pb-1">
                                <input type="text" name="route" class="font-weight-bold form-control-lg form-control menu-route" placeholder="Route">
                            </div>
                            <div class="form-group mb-4 pb-1">
                                <input type="text" name="slug" class="font-weight-bold form-control-lg form-control menu-slug" placeholder="Slug">
                            </div>

                            <div class="form-group mb-4 pb-1">
                                <input name="icon" class="font-weight-bold form-control-lg form-control" type="file">
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

            $('.table-menus').DataTable({
                processing: true,
                "language": {
                    processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
                ordering: false,
                serverSide: true,
                pageLength:100,
                ajax: "{!! route('get-menus') !!}",
                columns: [
                    { data: 'action', name: 'action' },
                    { data: 'title', name: 'title' },
                    { data: 'url', name: 'url' },
                    { data: 'route', name: 'route' },
                    { data: 'icon', name: 'icon' },
                ]

            });

            $(document).on('keyup', '.form-control', function(){
                $(this).removeClass('is-invalid');
                $(this).next().remove();
            });

            $(document).on('submit', '#add-menu-form', function(e){
                e.preventDefault();
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    }
                });
                $.ajax({
                    url: "{{ route('add-menu') }}",
                    method: 'post',
                    processData: false,
                    contentType: false,
                    data:new FormData(this),
                    beforeSend: function(){
                        $('.save-btn').val('Please wait...');
                        $('.save-btn').addClass('disabled');
                        $('.save-btn').attr('disabled', true);
                    },
                    success: function(result){
                        $('.save-btn').val('add');
                        $('#edit-menu-form .save-btn').removeClass('disabled');
                        $('.save-btn').attr('disabled', true);
                        $('.save-btn').removeAttr('disabled');
                        if(result.success === true){
                            $('.modal').modal('hide');
                            toastr.success('Success!', 'Menu added successfully',{"positionClass": "toast-bottom-right"});
                            $('#add-menu-form')[0].reset();
                            setTimeout(function(){
                                $('.table-menus').DataTable().ajax.reload();
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

            $(document).on('submit', '#edit-menu-form', function(e){
                e.preventDefault();
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    }
                });
                $.ajax({
                    url: "{{ route('edit-menu') }}",
                    method: 'post',
                    processData: false,
                    contentType: false,
                    data:new FormData(this),
                    beforeSend: function(){
                        $('#edit-menu-form .save-btn').val('Please wait...');
                        $('#edit-menu-form .save-btn').addClass('disabled');
                        $('#edit-menu-form .save-btn').attr('disabled', true);
                    },
                    success: function(result){
                        $('#edit-menu-form .save-btn').val('Update');
                        $('#edit-menu-form .save-btn').removeClass('disabled');
                        $('#edit-menu-form .save-btn').removeAttr('disabled');
                        if(result.success === true){
                            $('.modal').modal('hide');
                            toastr.success('Success!', 'Menu Updated successfully',{"positionClass": "toast-bottom-right"});
                            $('#edit-menu-form')[0].reset();
                            setTimeout(function(){
                                $('.table-menus').DataTable().ajax.reload();
                            }, 2000);

                        }


                    },
                    error: function (request, status, error) {
                        $('#edit-menu-form .save-btn').val('Update');
                        $('#edit-menu-form .save-btn').removeClass('disabled');
                        $('#edit-menu-form .save-btn').removeAttr('disabled');
                        $('#edit-menu-form .form-control').removeClass('is-invalid');
                        $('#edit-menu-form .form-control').next().remove();
                        json = $.parseJSON(request.responseText);
                        $.each(json.errors, function(key, value){
                            $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                            $('input[name="'+key+'"]').addClass('is-invalid');
                        });
                    }
                });
            });

            /*var imgeFolder = '<?php //echo asset("uploads/menu-icon/"); ?>';*/

            $(document).on('click', '.edit-icon',function(e){
                var sId = $(this).parents('tr').attr('id');
                var oldName = $(this).closest('tr').find("a").text();
                var oldUrl = $(this).parents('td').next().next().text();
                var oldRoute = $(this).parents('td').next().next().next().text();
                /*var oldIcon = $(this).closest('tr').find("img").attr("name");*/
                $('.menu-title').val(oldName);
                /*$('#edit-menu-form .menu-icon').attr("src", imgeFolder + "/" +oldIcon);
                $('#edit-menu-form .menu-icon').removeClass("hide");*/
                $('#edit-menu-form .menu-url').val(oldUrl);
                $('#edit-menu-form .menu-route').val(oldRoute);
                $('input[name=editid]').val(sId);
                $('#editMenuModal').modal('show');
            });

            $(document).on('click', '.delete-menu', function(){
                var id = $(this).data('id');
                var menu_name = $(this).data('menu_name');
                swal({
                        title: "Alert!",
                        text: "Are you sure you want to delete Menu  ["+ menu_name +"] ?",
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
                                url:"{{ route('delete-menu') }}",
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
                                        toastr.success('Success!', 'Menu Deleted successfully!',{"positionClass": "toast-bottom-right"});
                                        setTimeout(function(){
                                            $('.table-menus').DataTable().ajax.reload();
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
@endsection
