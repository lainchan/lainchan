<?php
	
	session_name(SESS_COOKIE);
	session_start();
	
	if(!isset($_SESSION['created'])) $_SESSION['created'] = time();
	
	if(!isset($_COOKIE[HASH_COOKIE]) || !isset($_COOKIE[TIME_COOKIE]) || $_COOKIE[HASH_COOKIE] != md5($_COOKIE[TIME_COOKIE].SALT)) {
		$time = time();
		setcookie(TIME_COOKIE, $time, time()+COOKIE_EXPIRE, '/', null, false, true);
		setcookie(HASH_COOKIE, md5(time().SALT), time()+COOKIE_EXPIRE, '/', null, false, true);
		$user = Array('valid' => false, 'appeared' => $time);
	} else {
		$user = Array('valid' => true, 'appeared' => $_COOKIE[TIME_COOKIE]);
	}
?>