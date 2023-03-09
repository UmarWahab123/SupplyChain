<?php 
	$html = str_replace('[[name]]', '<strong>'.$user->name.'</strong>', $template->content);
	$html = str_replace('[[email]]', $user->email, $html);
?>
{!! $html !!}