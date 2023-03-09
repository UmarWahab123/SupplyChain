<?php 
	$html = str_replace('[[name]]', '<strong>'.$sales->name.'</strong>', $template->content);
	$html = str_replace('[[email]]', $sales->email, $html);
	$html = str_replace('[[password]]', $password, $html);
	$html = str_replace('[[Link]]', "<a href='".url('login')."'>Login</a>", $html);
?>
{!! $html !!}