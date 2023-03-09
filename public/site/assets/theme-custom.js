var headerHeight = $('header.header').outerHeight();
$('body').css('padding-top', headerHeight);

  $(document).ready(function(){
    $("table").on( 'column-sizing.dt', function ( e, settings ) {
    var content_width = $('.entriesbg').width();
    var table_width = $("table").width();
    if(table_width < content_width){
      $(".dataTables_scrollHead").css( "width", "100%" );
      $(".dataTables_scrollHeadInner").css( "width", "100%" );
      $(".dataTables_scrollBody").css( "width", "100%" );
      $("table").css( "width", "100%" );
    }
});

$(".linkcounter").parents('.dropdown-menu').addClass('drp-counter');
$(".selected-item-btn").removeClass('btn');

 $(".sidebarin .sidebarnav .dropdown-item img").parent('.dropdown-item').addClass('d-item-with-img');




});