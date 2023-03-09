
// var date = new Date();
//     var y = date.getFullYear();
//     var m = date.getMonth() + 1;
//     var d = date.getDate();
//     d = (d < 10) ? '0' + d : d;

//     var first_day = '01/' + m + '/' + y;
//     var current_day = d + '/' + m + '/' + y;

// if (window.sessionStorage.getItem('fromDate') != null) {
// $("#from_date").datepicker('setDate', window.sessionStorage.getItem('fromDate'));
// }
// else{
//     date.setDate( date.getDate() - 30 );
// $("#from_date").datepicker('setDate',date);
// // $("#from_date").datepicker('setDate',first_day);

// }
// if (window.sessionStorage.getItem('toDate') != null) {
// $("#to_date").datepicker('setDate', window.sessionStorage.getItem('toDate'));
// }
// else{
// $("#to_date").datepicker('setDate', 'today');
// }

$('#dynamic_select').on('change', function () {
$('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
    });
    $("#loader_modal").modal('show');
    var url = $(this).val(); // get selected value
if (url) { // require a URL
    $from_date = $('#from_date').val();
    $to_date = $('#to_date').val();
    // window.sessionStorage.removeItem('fromDate');
    // window.sessionStorage.removeItem('toDate');
    // window.sessionStorage.setItem('fromDate', $from_date);
    // window.sessionStorage.setItem('toDate', $to_date);
    window.location = url; // redirect
}
return false;
});
