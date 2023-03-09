@extends('backend.layouts.layout')

@section('title','Import History | Supply Chain')

@section('content')
    <div class="row mb-0">
        <div class="col-md-8 title-col">
            <h4 class="maintitle">Reserved Quantity History</h4> 
        </div>
    </div>
    <div class="row entriestable-row mt-2">
        <div class="col-12">
            <div class="entriesbg bg-white custompadding customborder">
                <div class="table-responsive">
                    <table class="table entriestable table-bordered table-import-history text-center">
                        <thead>
                            <tr>
                                <th>PF #</th>                    
                                <th>Order/TD/PO Ref #</th>                    
                                <th>Quantity</th>                    
                                <th>Description</th>
                                <th>User</th>
                                <th>Warehouse</th>
                                <th>CQ</th>
                                <th>RQ</th>
                                <th>AQ</th>
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
            url: "{!! route('get-reserved-quantity-history') !!}",
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
                { data: 'pf', name: 'pf' },
                { data: 'ref_id', name: 'ref_id' },
                { data: 'quantity', name: 'quantity' },
                { data: 'desc', name: 'desc' },
                { data: 'user', name: 'user'},
                { data: 'warehouse', name: 'warehouse'},
                { data: 'c_q', name: 'c_q'},
                { data: 'r_q', name: 'r_q'},
                { data: 'a_q', name: 'a_q'}
            ]
            
        });
        // $(document).on('dblclick','.downloadable', function(){
        //     var file_name = $(this).attr("data-filename");
        //     alert(file_name);
        // });
    });
</script>
@stop