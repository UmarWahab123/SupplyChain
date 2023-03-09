<script type="text/javascript">
	$('.select2').select2();

	var table = $('.deployments-table').DataTable({
		"sPaginationType": "listbox",
		ordering: false,
		serverSide: true,
		ajax: {
			beforeSend: function(){
				$('#loader_modal').modal({
					backdrop: 'static',
					keyboard: false
				});
				$("#loader_modal").modal('show');
			},
		url: "{{route('get-deployments-data')}}"
		},
		columns: [
			{ data: 'action', name: 'action' },
			{ data: 'type', name: 'type' },
			{ data: 'url', name: 'url' },
			{ data: 'token', name: 'token' },
			{ data: 'price', name: 'price' },
			{ data: 'warehouse', name: 'warehouse' },
			{ data: 'created_by', name: 'created_by' },
			{ data: 'status', name: 'status' },
		],
		drawCallback: function(){
			$('#loader_modal').modal('hide');
		},
	});

	$.ajaxSetup({
        headers:
        { 'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content') }
    });

	$(document).on('click', '#add_new_deployment', function (e) {
		$('#deployment-Modal-title').text('Add New Deployment');
        $("#price").val(0).trigger('change');
        $("#warehouse").val(0).trigger('change');
		$('#deployment-Form').trigger('reset');
	});
	$(document).on('click', '.btn-save', function (e) {
		e.preventDefault();
		if ($('#type').val() == null || $('#type').val() == ''
			|| $('#url').val() == null || $('#url').val() == ''
			|| $('#price').val() == null || $('#price').val() == ''
			|| $('#warehouse').val() == null || $('#warehouse').val() == '') 
		{
			toastr.info('Info!', 'All fields are mendentry.' ,{"positionClass": "toast-bottom-right"});
			return false;
		}
		$.ajax({
			beforeSend: function(){
				$('#loader_modal').modal({
					backdrop: 'static',
					keyboard: false
				});
				$("#loader_modal").modal('show');
			},
	        url: '{{route("save-deployment-data")}}',
	        type: "POST",
	        dataType: 'JSON',
	        data: $('#deployment-Form').serialize(),
	        success: function (data) {
	            if (data.success == true) {
	            	toastr.success('Success!', 'Information Saved Successfully.' ,{"positionClass": "toast-bottom-right"});
	                table.ajax.reload();
	                $("#loader_modal").modal('hide');
	                $("#deployment-Modal").modal('hide');
	                $('#deployment-Form').trigger('reset');
	                $('#deployment-Modal-title').text('Add New Deployment');
	                $("#price").val(0).trigger('change');
	                $("#warehouse").val(0).trigger('change');
	                $("#deployment_id").val('');
	            }
	        }
   		});
	});

	$(document).on('click', '.btn-edit', function (e) {
		$('#deployment-Modal-title').text('Edit Deployment');
		let id = $(this).data('id');
		$('#deployment_id').val(id);
		let row = table.row($(this).parents('tr')).data();
		$('#type').val(row.type);
		$('#url').val(row.url);
		$("#price option:contains(" + row.price + ")").attr('selected', 'selected').trigger('change');
		$("#warehouse option:contains(" + row.warehouse + ")").attr('selected', 'selected').trigger('change');
	});

	$(document).on('click', '.btn-delete', function (e) {
		let id = $(this).data('id');
		swal({
			title: "Are you sure?",
			text: "You want to Delete the record?",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#DD6B55",
			confirmButtonText: "Yes, Delete it!",
			cancelButtonText: "Cancel",
			closeOnConfirm: true,
			closeOnCancel: true
			},
			function (isConfirm) {
				if(isConfirm)
				{
				    $.ajax({
						url: "{{ route('delete-deployment-data') }}",
						method: 'post',
						dataType: 'json',
						data: 'id='+id,
						beforeSend: function(){
							$('#loader_modal').modal({
							backdrop: 'static',
							keyboard: false
							});
							$("#loader_modal").modal('show');
						},
						success: function(result){
							if(result.success == true)
							{
								toastr.success('Success!', 'Deleted Succeeded !!!',{"positionClass": "toast-bottom-right"});
								table.ajax.reload();
								$("#loader_modal").modal('hide');
							}
						}
				    });
				}
			}
		);
	});

	$(document).on('change', '#status', function (e) {
		let id = $(this).data('id');
		let status = 0;
		if ($(this).is(":checked")) {
			status = 1;
		}
		$.ajax({
			beforeSend: function(){
				$('#loader_modal').modal({
					backdrop: 'static',
					keyboard: false
				});
				$("#loader_modal").modal('show');
			},
	        url: '{{route("save-deployment-status")}}',
	        type: "POST",
	        dataType: 'JSON',
	        data: {id:id, status:status},
	        success: function (data) {
	            if (data.success == true) {
	            	toastr.success('Success!', 'Information Saved Successfully.' ,{"positionClass": "toast-bottom-right"});
	                table.ajax.reload();
	            }
	        }
   		});
	});

</script>