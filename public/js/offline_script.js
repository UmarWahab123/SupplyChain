$(document).ready(function(){
    // console.log(Offline.check());
    $( document ).ajaxError(function( request, response, error ) {
	    if(response.status === 500 || response.status === 404){
	    $('#loader_modal').modal('hide');
	    swal({
	      title: "Alert!",
	      text: "Something went wrong. Click OK to reload the page.",
	      type: "info",
	      showCancelButton: false,
	      confirmButtonClass: "btn-danger",
	      confirmButtonText: "OK",
	      closeOnConfirm: true,
	      closeOnCancel: false
	    },
	      function(isConfirm){
	        if(isConfirm){
	          window.location.reload();
	        }
	      }
	    );
	  	}
	 });

    
});