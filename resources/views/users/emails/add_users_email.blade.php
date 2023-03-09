<?php 
	$html = str_replace('[[name]]', '<strong>'.@$user_role->name.'</strong>', @$template->content);
	$html = str_replace('[[email]]', @$user_role->email, @$html);
	$html = str_replace('[[password]]', @$password, @$html);
	$html = str_replace('[[Link]]', "<a href='".url('login')."'>Login</a>", @$html);
?>
{!! $html !!}