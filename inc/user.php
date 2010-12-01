<?php
	
	// Set the session name.
	session_name(SESS_COOKIE);
	
	// Set session parameters
	session_set_cookie_params(0, JAIL_COOKIES?ROOT:'/');
	
	// Start the session
	session_start();
	
	// Session creation time
	if(!isset($_SESSION['created'])) $_SESSION['created'] = time();
	
	if(!isset($_COOKIE[HASH_COOKIE]) || !isset($_COOKIE[TIME_COOKIE]) || $_COOKIE[HASH_COOKIE] != md5($_COOKIE[TIME_COOKIE].SALT)) {
		$time = time();
		setcookie(TIME_COOKIE, $time, time()+COOKIE_EXPIRE, JAIL_COOKIES?ROOT:'/', null, false, true);
		setcookie(HASH_COOKIE, md5(time().SALT), time()+COOKIE_EXPIRE, JAIL_COOKIES?ROOT:'/', null, false, true);
		$user = Array('valid' => false, 'appeared' => $time);
	} else {
		$user = Array('valid' => true, 'appeared' => $_COOKIE[TIME_COOKIE]);
	}
?>