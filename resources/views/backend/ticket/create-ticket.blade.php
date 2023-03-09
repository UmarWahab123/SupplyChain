<div class="modal fade" id="createTicketModal">
    <div class="modal-dialog modal-lg modal-dialog-centered parcelpop">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body ">
                <h3 class="text-capitalize fontmed text-center">New Ticket</h3>
                <div class="mt-3">
                    <form action="{{route('create-ticket')}}" id="createTicketForm" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-12">
                                <input type="text" name="title" class="font-weight-bold form-control-lg ticket-title ticket-title form-control" placeholder="Tilte (Required)" required="">
                            </div>
                            <div class="form-group col-12">
                                <textarea type="text" name="detail" class="font-weight-bold form-control-lg form-control summernote ticket-description" placeholder="Description (Required)"></textarea>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6 department_html">
                            </div>
                            <div class="form-group col-6">
                                <input type="file" class="form-control-file ticket-attachments" id="ticket-attachments" name="attachments[]" multiple="multiple" accept=".doc,.docx,.png,.jpg,.jpeg">
                                <small>i.e:<span class="text-info"> doc,docx,png,jpg,jpeg</span></small>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-12">
                                <input type="url" class="font-weight-bold form-control-lg form-control" name="url" placeholder="Enter Url (Optional)....">
                            </div>
                        </div>
                        <input type="hidden" name="auto_generate" value="0">
                        <input type="hidden" name="notification_email" value="{{Auth::user()->email}}">
                        <input type="hidden" name="role" value="{{Auth::user()->roles->name}}">
                        <input type="hidden" name="role_name" value="{{Auth::user()->name}}">
                        @if(Auth::user()->parent)
                        <input type="hidden" name="parent_email" value="{{Auth::user()->parent->email}}">
                        <input type="hidden" name="parent_role" value="{{Auth::user()->parent->roles->name}}">
                        @else
                        <input type="hidden" name="parent_email" value="">
                        <input type="hidden" name="parent_role" value="">
                        @endif
                        <div class="form-submit text-center">
                            <input type="submit" value="Create" class="btn btn-bg submit-ticket">
                            <!-- <input type="reset" value="close" class="btn btn-danger close-btn"> -->
                            <input type="reset" value="close" data-dismiss="modal" class="btn btn-danger close-btn">

                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    /*ticket model*/
    $(document).on('click', '.create-ticket', function(e) {
        $.ajax({
            method: "get",
            dataType: "json",
            url: "{{ route('ticket-departments') }}",
            beforeSend: function() {
                $('#loader_modal').modal({
                    backdrop: 'static',
                    keyboard: false
                });
                $("#loader_modal").modal('show');
            },
            success: function(data) {
                $('.department_html').html('');
                $("#loader_modal").modal('hide');
                $('.department_html').append(data.html);
                $('.selectpicker').selectpicker('refresh');
                $('#createTicketModal').modal('show');

            }
        });
    });
    $(document).on('submit', '#createTicketForm', function(e) {
        e.preventDefault();
        var x = document.getElementById("ticket-attachments");
        ticket_detail = $('.ticket-description').val();
        var files = x.files;
        var check_file_type = true;
        $.each(files, function(i, file) {
            // ext.push(file.name.split('.').pop().toLowerCase());
            if ($.inArray(file.name.split('.').pop().toLowerCase(), ['doc', 'docx', 'png', 'jpg', 'jpeg']) == -1) {
                check_file_type = false;
            }
        });
        if (check_file_type == true && ticket_detail != '') {
            var token = "{{config('services.ticket.api_key')}}";
            var formData = new FormData($(this)[0]);
            var headers = {
                "Authorization": "Bearer " + token,
                "Accept": "application/json",
            }
            proxyurl = "https://cors-anywhere.herokuapp.com/";
            url = "https://support.thegemcloud.com/api/new-ticket";
            // url ="http://localhost:8000/ticketing/api/new-ticket";
            $.ajaxSetup({
              headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
              }
            });
            $.ajax({
                dataType: 'json',

                method: "post",
                // headers: {
                //     "Authorization": "Bearer " + token,
                //     "Accept": "application/json",
                // },
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                url:"{{ route('create-ticket') }}",
                beforeSend: function() {
                    $('#loader_modal').modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                    $("#loader_modal").modal('show');
                    $('.submit-ticket').attr('disabled', true);
                },
                success: function(data) {
                    // console.log(data);
                    $('.submit-ticket').removeAttr('disabled');
                    $('#createTicketModal').modal('hide');
                    if (data.success == true) {
                        $("#loader_modal").modal('hide');
                        toastr.success('Success!', 'Ticket Created Successfully!', {
                            "positionClass": "toast-bottom-right"
                        });
                        setTimeout(function() {
                            // window.location.reload();
                            window.location.href = "{{ url('/tickets') }}";
                        }, 2000);
                    }
                    if (data.success == false) {
                        $("#loader_modal").modal('hide');
                        toastr.error('error!', data.message, {
                            "positionClass": "toast-bottom-right"
                        });
                    }
                }
            });
        } else {
            if (ticket_detail == '') {
                toastr.error('Error!', "Please fill ticket detail section", {
                    "positionClass": "toast-bottom-right"
                });
            }
            if (check_file_type == false) {
                toastr.warning('Warning!', "Please select valid file type", {
                    "positionClass": "toast-bottom-right"
                });
            }

        }

    });

</script>
