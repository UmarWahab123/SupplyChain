<?php 
	$html = str_replace('[[name]]', '<strong>'.$supplier->first_name." ".$supplier->last_name.'</strong>', $template->content);
	$html = str_replace('[[email]]', $supplier->email, $html);
	$html = str_replace('[[Link]]', "<a href='".route('password.request')."'>Reset Password</a>", $html);
?>
{!! $html !!}