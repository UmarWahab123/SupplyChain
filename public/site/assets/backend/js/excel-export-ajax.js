function export_excel(url, data) {
    $.ajax({
        method: "post",
        url: url,
        data: data,
        beforeSend: function() {
            $('.export-alert').removeClass('d-none');
            $('.export-alert-success').addClass('d-none');
        },
        success: function(data) {
            if (data.success == true) {
                $('.export-alert').addClass('d-none');
                $('.export-alert-success').removeClass('d-none');
            }
        },
        error: function(request, status, error) {
            $("#loader_modal").modal('hide');
        }
    });
}