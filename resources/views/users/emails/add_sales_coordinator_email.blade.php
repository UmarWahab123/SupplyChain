<?php 
	$html = str_replace('[[name]]', '<strong>'.@$sales_co->name.'</strong>', @$template->content);
	$html = str_replace('[[email]]', @$sales_co->email, @$html);
	$html = str_replace('[[password]]', @$password, @$html);
	$html = str_replace('[[Link]]', "<a href='".url('login')."'>Login</a>", @$html);
?>
{!! $html !!}