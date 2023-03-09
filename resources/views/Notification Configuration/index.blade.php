@extends('backend.layouts.layout')

@section('title','Notifications Configuration | Supply Chain')
@section('content')
<style type="text/css">
.selectDoubleClick, .inputDoubleClick{
  font-style: italic;
  font-weight: bold;
}

/* Chrome, Safari, Edge, Opera */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
-webkit-appearance: none;
margin: 0;
}

/* Firefox */
input[type=number] {
-moz-appearance:textfield;
}
.select2-container {
  width: 100% !important;
}
.annual-check
{
	width: 12px;
    height: 12px;
    background: white;
    border-radius: 50%;
    border: 4px solid blue;
}

.monthly-check
{
	width: 12px;
    height: 12px;
    background: white;
    border-radius: 50%;
    border: 4px solid blue;
}
.ck.ck-content.ck-editor__editable {
    height: 180px;
    margin-left: 3px;
}

</style>
@php
use Carbon\Carbon;
@endphp
<div class="row align-items-center left-right-padding mb-2 form-row">
    <div class=" col-lg-3 col-md-4">
  <h3 class="custom-customer-list"> Notifications Configuration </h3>
</div>
{{-- button add new configuration --}}
<div class="col-lg-9 col-md-8">
  <div class="pull-right d-flex">
    @if(Auth::user()->role_id==8)
              <button class="btn recived-button text-nowrap mr-2" id="addNewConfigugration" data-toggle="modal" data-target="#NewNotificationConfiguration">Add New Configuration</button>
    @endif
  </div>
</div>
</div>

{{-- Modal to add new configuration  --}}
<div class="row mb-3">
	<div class="col-md-12">
  <!-- Modal -->
  <div class="modal fade"  id="NewNotificationConfiguration"  tabindex="-1" role="dialog" aria-labelledby="NewNotificationConfigurationLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="NewNotificationConfigurationLabel">New Notification Details</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <form action="" id="addNewConfiguration">
                @csrf
                <input type="hidden" value="" id="dbId" name="dbId">
                <div class="form-group col-12">
                    <label style="float:left">Name  <small class="text-danger"> *(100 words)</small></label>
                    <input type="text" id="notification_name" minlength="1" maxlength="100"   name="notification_name"  class="font-weight-bold form-control-lg form-control" value="">
                    <div class="error text-danger">
                        <span id="notification_name_error"></span>
                    </div>
                </div>
                <div class="form-group col-12" id="slug_div">
                    <label style="float:left">Slug  <small class="text-danger"> *(100 words)</small></label>
                    <input type="text" id="slug" minlength="1" maxlength="100"   name="slug"  class="font-weight-bold form-control-lg form-control" value="">
                    <div class="error text-danger">
                        <span id="slug_error"></span>
                    </div>
                </div>
                <div class="form-group col-12">
                    <label style="float:left">Discription  <small class="text-danger">*(250 words)</small></label><br>
                    <textarea name="notification_discription" minlength="1" maxlength="250" id="notification_discription" class="form-control-lg form-control"  rows="3" cols="150"></textarea>
                    <div class="error text-danger">
                        <span id="notification_discription_error"></span>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary " id="StorenewConfiguration" >Save</button>
        </div>
      </div>
    </div>
  </div>

	</div>

</div>

{{-- Modal for setting configuration  --}}
<div class="row mb-3">
	<div class="col-md-12">
  <!-- Modal -->
  <div class="modal fade"  id="configurationDetails"  tabindex="-1" role="dialog" aria-labelledby="configurationDetailsLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="configurationDetailsLabel">Configurations</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <form action="" id="addNewConfigurationTemplate" >
                @csrf
                {{-- notification_configuration record id --}}
                <input type="hidden" name="configuration_id" id="configuration_id">
                <div class="row">
                    {{-- configuration details --}}
                    <div class="col-8">
                        <div class="form-group">
                            <label for="subject" style="float:left" class="font-weight-bold">Configuration For</label>
                            <select class="font-weight-bold form-control-lg form-control js-states state-tags" name="notification_type" id="notification_type" onchange="getConfigurationSavedDetails(this.value)">
                                <option value="" selected disabled>Configuration For</option>
                                <option value="notification">Notification</option>
                                <option value="email">Email</option>
                            </select>
                            <div class="error text-danger">
                                <p id="notification_type_error"></p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="subject" style="float:left" class="font-weight-bold">Subject</label>
                            <input type="text" name="subject" class="form-control" id="subject">
                            <div class="error text-danger">
                                <p id="subject_error"></p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="bodydiscription" style="float:left">Body</label><br>
                            <textarea class="form-control mr-3" rows="30"  name="bodydiscription" placeholder="Body" id="bodydiscription"></textarea>
                            <input type="hidden" name="body" id="body">
                            <div class="error text-danger">
                                <p id="body_error"></p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="emails" style="float:left" >Select Type</label><br>
                            <select name="user_type" class="selectpicker form-control" id="user_type"    data-live-search="true" onchange="checkSelectedDropDownValue()">
                                <option  selected="selected">Select Type </option>
                                <option value="roles">Roles</option>
                                <option value="users">Users</option>
                                <option value="emails_custom">Custom Emails</option>
                            </select>
                            <div class="error text-danger">
                                <p id="user_type_error"></p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="emails" style="float:left" >Select User</label><br>
                            <select name="configured_users" class="selectpicker form-control" id="configured_users"  multiple  data-live-search="true">

                            </select>
                            <input type="hidden" name="all_user_ids" class="all_user_ids">
                            <div class="error text-danger">
                                <p id="configured_users_error"></p>
                            </div>
                        </div>
                    </div>
                    {{-- /configuration details  --}}
                    {{-- Keywords --}}
                    <div class="col-4">
                        <div class="card mt-4">
                            @if(Auth::user()->role_id==8)
                            <h5 class="card-header">Keywords <a class="btn btn-default pull-right" onclick="displayKeywordField()" >+</a> </h5>
                            @endif
                            <div class="card-body">
                              <h5 class="card-title"><strong>Note:</strong>Copy and paste following keywords to editor</h5>
                                <input class="d-none form-controls" type="text" placeholder="Keyword"  value="" id="keywords" name="keywords">
                                <span id="keyword_placement"></span>
                            </div>
                          </div>
                    </div>
                    {{-- /Keywords --}}
                </div>
            </div>
            {{-- Cusutom Message --}}
            <div class="col-6">
                <p class="text-primary">* To Add Custome Email click <b>Add Custom Mail</b> button below </p>
                <p class="text-primary">* After Typing In email Field <b>Click Outside</b>  </p>
            </div>
            {{-- Cusutom Message --}}

                     {{-- Add New Custom Mail --}}
                     <div class="d-flex flex-row-reverse col-8" style="height:20px;">
                        <input type="email" placeholder="Custom Email" class="form-control border border-danger mb-3 d-none" name="custom_email" id="custom_email">
                    </div>
                    <div class="error text-danger col-6">
                        <p id="custom_email_error"></p>
                    </div>
                    {{-- /Add New Custom Mail --}}

            <div class="modal-footer mt-3">
                <a class="btn btn-default" onclick="displayCustomEmailField()" >Add Custom Mail</a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveConfigurationSetting" >Save</button>

            </div>
        </form>
        </div>
    </div>
  </div>

	</div>

</div>




{{-- table to display records --}}
<div class="row">
    <div class="col">

        <table class="table entriestable table-bordered   text-center bg-white" id="configuration_table" width="100%">
            <thead>
                <tr>
                    <th style="width:10%">Action</th>
                    <th style="width:20%" id="short_desc">Name</th>
                    <th style="width:40%" id="short_desc">Description</th>
                    <th style="width:20%">Types</th>
                    <th style="width:10%">Status</th>
                </tr>
            </thead>
        </table>
    </div>
</div>



@endsection

@section('javascript')

<script type="text/javascript">
var myEditor;
// csrf Token
    $.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
  }
});

//ckeditor for body content
$(function(e){
    ClassicEditor
        .create( document.querySelector( '#bodydiscription' ) )
        .then( editor => {
            console.log( 'Editor was initialized', editor );
            myEditor = editor;
        } )
        .catch( error => {
            console.error( error );
        });

});
// data table
$(function() {
        $('#configuration_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{route('getNotificationConfigurationRecords')}}",
            columns: [
            {data: 'action', name: 'action'},
            {data: 'notification_name', name: 'notification_name'},
            {data: 'notification_discription', name: 'notification_discription'},
            {data: 'notification_type', name: 'notification_type'},
            {data: 'notification_status', name: 'notification_status'}
        ]
        });

    });
// Submit New Configuration
$( "#StorenewConfiguration" ).click(function() {
    let notification_discription=$("#notification_discription").val();
    if($('#notification_name').val()==''){
        $("#notification_name_error").html('Name Required *');
        return;
    }
    else if(!($('#notification_name').val()=='')){
        $("#notification_name_error").html('');
    }

    if(notification_discription==''){
        $("#notification_discription_error").html('Discription Required *');
        return;
    }
    else if(!(notification_discription=='')){
        $("#notification_discription_error").html('');
    }

    if($('#slug').val()=='' && $('#StorenewConfiguration').html() == 'Save'){
        $("#notification_discription_error").html('Slug Required *');
        return;
    }
    else if(!($('#slug').val()=='')){
        $("#notification_discription_error").html('');
    }
    $.ajax({
      method: "post",
      url: "{{ route('saveNotificationConfiguration') }}",
      dataType: 'json',
      data:$("#addNewConfiguration").serialize(),
      success: function(data)
      {
          if (data.success==true) {
              toastr.success('Success!', 'Configuration Added successfully.',{"positionClass": "toast-bottom-right"});
            }
          if(data.success=='updated')
            {
                toastr.success('Success!', 'Configuration Updated successfully.',{"positionClass": "toast-bottom-right"});
            }
            $("#NewNotificationConfiguration").modal('hide');
            $('#configuration_table').DataTable().ajax.reload();

      }
    });
});
// update Notification Type in database
function updateNotifcationType(notificationTypeName,dbRecordId){
  event.preventDefault();
  $.ajax({
      method: "post",
      url: "{{ route('saveNotificationConfiguration') }}",
      dataType: 'json',
      data:{'action':'updateNotificationType','notificationTypeName':notificationTypeName,'dbRecordId':dbRecordId},
      success: function(data)
      {
          $('#configuration_table').DataTable().ajax.reload();
          if (data.success==true) {
              toastr.success('Success!', 'Notification type Updated successfully.',{"positionClass": "toast-bottom-right"});
            }
      }
    });
}
// update Notification status
function updateNotficationStatus(statusTypeValue,dbRecordId){
    event.preventDefault();
  $.ajax({
      method: "post",
      url: "{{ route('saveNotificationConfiguration') }}",
      dataType: 'json',
      data:{'action':'updateStatusType','statusTypeValue':statusTypeValue,'dbRecordId':dbRecordId},
      success: function(data)
      {
          $('#configuration_table').DataTable().ajax.reload();
          if (data.success==true) {
              toastr.success('Success!', 'Notification Status Updated successfully.',{"positionClass": "toast-bottom-right"});
            }
      }
    });
}
// update configuration detail
function updateCongifugrationDetail(dbRecordId){
    $("#dbId").val(dbRecordId);
      $("#StorenewConfiguration").html('Update');
      $('#slug_div').addClass('d-none');
    $("#NewNotificationConfigurationLabel").html('Update Configuration Detail');
    $.ajax({
      method: "post",
      url: "{{ route('getNotificationDetail') }}",
      dataType: 'json',
      data:{'dbRecordId':dbRecordId},
      success: function(data)
      {
        $('#notification_discription').val(data.notification_discription);
        $('#notification_name').val(data.notification_name);
      }
    });
}
// Remove data from modals
//New Notification Configuration Modal
$('#NewNotificationConfiguration').on('hidden.bs.modal', function () {
    $(this).find('form').trigger('reset');
});
//Configuration Details Modal
$('#configurationDetails').on('hidden.bs.modal', function () {
    $(this).find('form').trigger('reset');
});
// Updating button name and title of modal
$('#addNewConfigugration').on('click',function(){
        $("#StorenewConfiguration").html('Save');
        $("#NewNotificationConfigurationLabel").html('New Notification Details');
         $('#slug_div').removeClass('d-none');
});


// store configuration detail
 $("#saveConfigurationSetting").click( function(){
    let configured_users=$('#configured_users').val();
    let notification_type=$("#notification_type").val();
    let subject=$("#subject").val();
    let user_type=$("#user_type").val();
    let body=myEditor.getData();
    let all_user_ids=$("#all_user_ids").val();
    $('#body').val(myEditor.getData());
    $('.all_user_ids').val(configured_users);
    if(!(notification_type)){
        $("#notification_type_error").html('Select Notification Type');
    }else if(!(subject)){
        $("#notification_type_error").html('');
        $("#subject_error").html('Select Subject');
    }else if(!(body)){
        $("#notification_type_error").html('');
        $("#subject_error").html('');
        $("#body_error").html('Body Is Required');
    }else if(!(user_type))
    {
        $("#notification_type_error").html('');
        $("#subject_error").html('');
        $("#body_error").html('');
        $("#user_type_error").html("Type Required");
    }else if(!(configured_users)){
        $("#notification_type_error").html('');
        $("#subject_error").html('');
        $("#body_error").html('');
        $("#user_type_error").html("");
        $("#configured_users_error").html("Recipent Required");
    }
    else{
        $.ajax({
          method: "post",
          url: "{{ route('saveNotificationTemplate') }}",
          dataType: 'json',
          data:$("#addNewConfigurationTemplate").serialize(),
          success: function(data)
          {

              if (data.success==true) {
                  toastr.success('Success!',data.msg,{"positionClass": "toast-bottom-right"});
                }
                $("#body_error").val('');
                $("#notification_type_error").html('');
                $("#subject_error").html('');
                $("#user_type_error").html("");
                $("#configured_users_error").html("");
                $("#configurationDetails").modal('hide');
          }
        });
    }

 });


// setting configuration id for record  configuration setting
function setCurrentRecordIdForConfiguration(notification_configuration_id){
$("#configuration_id").val(notification_configuration_id);
$("#keywords").addClass("d-none");
$("#subject").val('');
$("#keyword_placement").html('');
$("#custom_email_error").html('');
$("#custom_email").addClass("d-none");
 myEditor.setData('');
 $("#user_type").val("");
 $("#user_type").selectpicker('refresh');
 $("#configured_users").val("");
 $("#configured_users").selectpicker('refresh');
}
//select user on basis of type
function checkSelectedDropDownValue(){
    event.preventDefault();
let userDefinedForConfigurations=$("#user_type").val();
      $.ajax({
      method: "post",
      url: "{{ route('getSelectedUser')}}",
      dataType: 'json',
      data:{'userDefinedForConfigurations':userDefinedForConfigurations},
      success: function(data)
      {
        $("#configured_users").html('');
        $("#configured_users").html(data.html);
        $("#configured_users").selectpicker('refresh');
      }
    });
}
// get template detail on basis of configuration
function getConfigurationSavedDetails(notification_type){
    let configuration_id=$("#configuration_id").val();
    let configuredIds=[];
    $.ajax({
      method: "post",
      url: "{{ route('getSelectedConfigurationContent')}}",
      data:{'configuration_id':configuration_id,'notification_type':notification_type},
      success: function(data)
      {
        // setting data to bodyDiscription
              myEditor.setData(data.body);
              $("#keyword_placement").html(data.html_keywords_template);
            $("#subject").val(data.subject);
            $("#user_type").val(data.user_type);

            $("#user_type").selectpicker('refresh');

            checkSelectedDropDownValue();
            if(data.configured_users){
                var configuredIds = data.configured_users.split(',').map(Number);
            }
            setTimeout(function(){
                $("#configured_users").val(configuredIds);
                $("#configured_users").selectpicker('refresh');
             }, 1000);
      }
    });
 }
//  display keyword field
function displayKeywordField(){
    $("#keywords").val('');
    $("#keywords").removeClass("d-none");
}
//  add keyword against configuration settings
$( "#keywords" ).focusout(function() {
    let keyword=$("#keywords").val();
    let configuration_id=$("#configuration_id").val();
    let notification_type=$("#notification_type").val();
    if(notification_type && keyword ){
        $.ajax({
      method: "post",
      url: "{{ route('saveKeywordAgainstConfiguration')}}",
      dataType: 'json',
      data:{'configuration_id':configuration_id,'keyword':keyword,'notification_type':notification_type},
      success: function(data)
      {
        if (data.success==true) {
              toastr.success('Success!',data.msg,{"positionClass": "toast-bottom-right"});
              $("#configurationDetails").modal('hide');
        }
      }
    });
    }else{
    alert('Select Notification Type To Store Keyword or Fill keyword');
    }
  });
//   Add Custome Email
function displayCustomEmailField(){
    $("#custom_email").removeClass('d-none');
}
// Submit Custom Email Field Value
$("#custom_email").focusout(function() {
    event.preventDefault();
    let custom_email=$("#custom_email").val();
    if(!(custom_email)){
        $("#custom_email_error").html("Custom Email Required");
    }
    else{
      $.ajax({
      method: "post",
      url: "{{ route('saveCustomEmail') }}",
      dataType: 'json',
      data:{'custom_email':custom_email},
      success: function(data)
      {
          if (data.success==true) {
              toastr.success('Success!',data.msg,{"positionClass": "toast-bottom-right"});
            }
        $("#custom_email_error").html('');
        $("#custom_email").addClass('d-none');
        $("#custom_email").val('');
      }
    });

    }
  });
</script>
@endsection
