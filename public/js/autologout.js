const APP_URL = $("#get_url").val();
var sessServerAliveTime = 10 * 60 * 200; // 2 mints
var sessionTimeout = 1 * 3600000;
var sessLastActivity;
var idleTimer, remainingTimer;
var isTimout = false;

var sess_intervalID, idleIntervalID;
var sess_lastActivity;
var timer;
var isIdleTimerOn = false;
localStorage.setItem('sessionSlide', 'isStarted');

$(document).ready(function() {
	$(function () {
	    function checkPendingRequest() {
	        if ($.active > 0) {
	            window.setTimeout(checkPendingRequest, 1000);
	            startIdleTime();
			    clearInterval(remainingTimer);
			    localStorage.setItem('sessionSlide', 'isStarted');
	        }
	    };
	    window.setTimeout(checkPendingRequest, 1000);
	 });

	$(".modal").click(function(ed, e){
	    const modelID = $(this).attr("id");
	    if(modelID != 'session-expire-warning-modal'){
	    	$(this).bind('click mouseup mousedown keydown keypress keyup submit change mouseenter scroll resize dblclick', function (ed, e) {
		    	
		    	startIdleTime();
			    clearInterval(remainingTimer);
			    localStorage.setItem('sessionSlide', 'isStarted');
	    	});
	    }
	});
	$(window).scroll(function (e) {
	    localStorage.setItem('sessionSlide', 'isStarted');
	    startIdleTime();
	});
	$(window).mousemove(function (e) {
		if(!$("#session-expire-warning-modal").hasClass('show')){
			localStorage.setItem('sessionSlide', 'isStarted');
	    	startIdleTime();
		}
	    
	});

	var sessionExpired = document.getElementById("session-expired-modal");
	function sessionExpiredClicked(evt) {
		sessLogOut(evt);
	}

	sessionExpired.addEventListener("click", sessionExpiredClicked, false);
	
	$("#btnOk").click(function (e) {
	     e.preventDefault()
	    $("#session-expire-warning-modal").modal('hide');
	    $('.modal-backdrop').css("z-index", parseInt($('.modal-backdrop').css('z-index')) - 500);
	    startIdleTime();
	    clearInterval(remainingTimer);
	    localStorage.setItem('sessionSlide', 'isStarted');
	});

	$('#session-expired-modal').on('shown.bs.modal', function () {
	    $("#session-expire-warning-modal").modal('hide');
	    $(this).before($('.modal-backdrop'));
	    $(this).css("z-index", parseInt($('.modal-backdrop').css('z-index')) + 1);
	});

	$("#session-expired-modal").on("hidden.bs.modal", function (evnt) {
		evnt.preventDefault();
	    localStorage.setItem('sessionSlide', 'loggedOut');
	    sessLogOut(evnt);
	});
	$('#session-expire-warning-modal').on('shown.bs.modal', function () {
	    $("#session-expire-warning-modal").modal('show');
	    $(this).before($('.modal-backdrop'));
	    $(this).css("z-index", parseInt($('.modal-backdrop').css('z-index')) + 1);
	});

})

function stopIdleTime() {
	    clearInterval(idleIntervalID);
	    clearInterval(remainingTimer);
	}
function sessKeyPressed(ed, e) {
    var target = ed ? ed.target : window.event.srcElement;
    var sessionTarget = $(target).parents("#session-expire-warning-modal").length;

    if (sessionTarget != null && sessionTarget != undefined) {
        if (ed.target.id != "btnSessionModal" && ed.currentTarget.activeElement.id != "session-expire-warning-modal" && ed.target.id != "btnExpiredOk"
             && ed.currentTarget.activeElement.className != "modal fade modal-overflow in" && ed.currentTarget.activeElement.className != 'modal-header'
    && sessionTarget != 1 && ed.target.id != "session-expire-warning-modal") {
            localStorage.setItem('sessionSlide', 'isStarted');
            startIdleTime();
        }
    }
}

function startIdleTime() {
    stopIdleTime();
    localStorage.setItem("sessIdleTimeCounter", $.now());
    idleIntervalID = setInterval('checkIdleTimeout()', 1000);
    isIdleTimerOn = false;
}

function checkIdleTimeout() {
	var idleTime = (parseInt(localStorage.getItem('sessIdleTimeCounter')) + (sessionTimeout)); 
    if ($.now() > idleTime + 60000) {

    	$("#session-expire-warning-modal").modal('hide');
        $("#session-expired-modal").modal('show');
        clearInterval(sess_intervalID);
        clearInterval(idleIntervalID);

        $('.modal-backdrop').css("z-index", parseInt($('.modal-backdrop').css('z-index')) + 100);
        $("#session-expired-modal").css('z-index', 2000);
        $('#btnExpiredOk').css('background-color', '#428bca');
        $('#btnExpiredOk').css('color', '#fff');
        sessExpire();
        isTimout = true;
        
        sessLogOut();

    }
    else if ($.now() > idleTime) {
        ////var isDialogOpen = $("#session-expire-warning-modal").is(":visible");
        if (!isIdleTimerOn) {
            ////alert('Reached idle');
            localStorage.setItem('sessionSlide', false);
            countdownDisplay();

            $('.modal-backdrop').css("z-index", parseInt($('.modal-backdrop').css('z-index')) + 500);
            $('#session-expire-warning-modal').css('z-index', 1500);
            $('#btnOk').css('background-color', '#428bca');
            $('#btnOk').css('color', '#fff');
            $("#seconds-timer").empty();
            $("#session-expire-warning-modal").modal('show');


            isIdleTimerOn = true;
        }
    }
}

function sessPingServer() {
	    if (!isTimout) {
	        return true;
	    }
	}

	function sessServerAlive() {
	    sess_intervalID = setInterval('sessPingServer()', sessServerAliveTime);
	}

	function initSessionMonitor() {

	    $(document).bind('keypress.session', function (ed, e) {
	        sessKeyPressed(ed, e);
	    });
	    $(document).bind('click mouseup mousedown keydown keypress keyup submit change mouseenter scroll resize dblclick', function (ed, e) {

	        sessKeyPressed(ed, e);
	    });
	    sessServerAlive();
	    startIdleTime();
	}

function sessionExpiredRedirect(evnt){
    localStorage.setItem('sessionSlide', 'loggedOut');
    $("#session-expired-modal").modal('hide');
    sessLogOut();
}

function countdownDisplay() {

    var dialogDisplaySeconds = 60;

    remainingTimer = setInterval(function () {
        if (localStorage.getItem('sessionSlide') == "isStarted") {
            $("#session-expire-warning-modal").modal('hide');
            startIdleTime();
            clearInterval(remainingTimer);
        }
        else if (localStorage.getItem('sessionSlide') == "loggedOut") {         
            $("#session-expire-warning-modal").modal('hide');
            $("#session-expired-modal").modal('show');
        }
        else {

            $('#seconds-timer').html(dialogDisplaySeconds);
            dialogDisplaySeconds -= 1;
        }
    }
    , 1000);
};
function sessExpire(){
    $.ajax({
        url:APP_URL+"/logout-user",
        type: "GET",
        success: function (result) {
            if (result == "true") {
                return true;
            }
        }
    });
}

function sessLogOut() {
    $.ajax({
        url:APP_URL+"/processing-status",
        type: "GET",
        success: function (result) {
            if (result == "true") {
            	$('#logout-form').submit();
            }else{
            	location.href = result;
            }
        }
    });

}