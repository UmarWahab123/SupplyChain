@extends('backend.layouts.layout')

@section('title','Import History | Supply Chain')

@section('content')
    <div class="row mb-0">
        <div class="col-md-8 title-col">
            <h4 class="maintitle">Import Files History</h4> 
        </div>
    </div>
    <div class="row entriestable-row mt-2">
        <div class="col-12">
            <div class="entriesbg bg-white custompadding customborder">
                <div class="table-responsive">
                    <table class="table entriestable table-bordered table-import-history text-center">
                        <thead>
                            <tr>
                                <th>User Name</th>                    
                                <th>Page Name</th>                    
                                <th>File</th>                    
                                <th>Created At</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        
                    </table>
                </div>  
            </div>
        </div>
    </div>
@endsection

@section('javascript')
<script type="text/javascript">
    $(function(e){
        var table2 = $('.table-import-history').DataTable({
            ajax: 
            {
            url: "{!! route('get-import-file-history') !!}",
            // data: function(data) { data.select_country = $('.select_country option:selected').val() },
            
            },
            processing: true,
            "language": {
                processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
            ordering: false,
            serverSide: true,
            pageLength: {{100}},
            scrollX: true,
            scrollY : '90vh',
            scrollCollapse: true,
            lengthMenu: [ 100, 200, 300, 400],


            columns: [
                { data: 'user_name', name: 'user_name' },
                { data: 'page_name', name: 'page_name' },
                { data: 'file', name: 'file' },
                { data: 'created_at', name: 'created_at' },
                { data: 'time', name: 'time'}
            ]
            
        });
        // $(document).on('dblclick','.downloadable', function(){
        //     var file_name = $(this).attr("data-filename");
        //     alert(file_name);
        // });
    });
</script>
@stop