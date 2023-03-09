$(document).on('change', '#sales_person_select', function () {
    var user_id = $(this).val();
    $.ajax({
      url: config.routes.zone,
      method: 'get',
      data: {id:id, user_id:user_id, type:type},
      context: this,
      beforeSend: function(){
        $(".draft_quotation_copy_btn").prop('disabled', true);
        $(".draft_quotation_save_btn").prop('disabled', true);
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").modal('show');
      },
      success: function(result)
      {
        if (result.success == true) {
          $("#loader_modal").modal('hide');
          $('#user_id').val(user_id);

          $('#save-and-close-btn').attr('disabled', false);
          $('#confirm-quotation-btn').attr('disabled', false);
          toastr.success('Success!', 'Information Updated Successfully.' ,{"positionClass": "toast-bottom-right"});

        }
        else{
            toastr.error('Error!', 'Somethig Went Wrong. Please contact System Administrator.' ,{"positionClass": "toast-bottom-right"});
        }
      }

    });
  });