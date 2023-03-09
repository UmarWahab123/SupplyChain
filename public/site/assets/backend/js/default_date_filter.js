var date = new Date();
var y = date.getFullYear();
var m = date.getMonth() + 1;
var d = date.getDate();
d = (d < 10) ? '0' + d : d;

var first_day = '01/' + m + '/' + y;
var current_day = d + '/' + m + '/' + y;
$('.from_date, #from_date, #date_delivery_filter_from').val(first_day);
$('.to_date, #to_date, #date_delivery_filter_to').val(current_day);
