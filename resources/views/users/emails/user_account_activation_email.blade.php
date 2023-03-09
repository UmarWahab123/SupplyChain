<?php 
	$html = str_replace('[[name]]', '<strong>'.$user->name.'</strong>', $template->content);
	$html = str_replace('[[email]]', $user->email, $html);
	$html = str_replace('[[Link]]', "<a href='".route('password.request')."'>Reset Password</a>", $html);
?>
{!! $html !!}