<script type="text/javascript">
	var _token = $('input[name="_token"]').val();
  $(document).on('click', '.bell-dropdown', function (e) {
  e.stopPropagation();
})


  $('.cross-btn').click(function(e) {
    e.stopPropagation();
    var id = $(this).data("id");
    $.ajax({
      method: "post",
      dataType: 'json',
      data: {id:id, _token:_token},
      url: "{{route('clear-notification')}}",
      success: function (responce) {
        $('#grand-parent-div-'+id).removeClass('unread');
        $('#parent-div-'+id).addClass('d-none');
        var counter = $('.badge').html();
        counter = counter-1;
        if(counter != 0){
          $('.badge').html(counter);
        }
        else
        {
          $('.badge').html('');
        }
        if (responce.count == 0) {
          $('.clear-all').addClass('d-none');
        }
      }
    });
    
  });

  $('.clear-all').click(function(e) {
    $.ajax({
      url: "{{route('clear-all-notifications')}}",
      method: "post",
      data:{ _token:_token},
      success: function (responce) {
      	$('.bell-dropdown').removeClass('show');
        $('.unread').removeClass('unread');
        $('.cross-btn').addClass('d-none');
        $('.badge').html('');
        $('.clear-all').addClass('d-none');
      }
    });
  });

</script>
