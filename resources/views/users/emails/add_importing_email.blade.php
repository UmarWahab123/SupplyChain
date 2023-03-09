<?php 
	$html = str_replace('[[name]]', '<strong>'.@$importing->name.'</strong>', @$template->content);
	$html = str_replace('[[email]]', @$importing->email, @$html);
	$html = str_replace('[[password]]', @$password, @$html);
	$html = str_replace('[[Link]]', "<a href='".url('login')."'>Login</a>", @$html);
?>
{!! $html !!}