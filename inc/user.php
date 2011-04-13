<?php
	if($_SERVER['SCRIPT_FILENAME'] == str_replace('\\', '/', __FILE__)) {
		// You cannot request this file directly.
		header('Location: ../', true, 302);
		exit;
	}
	
	// 'false' means that the user is not logged in as a moderator
	$mod = false;
	
	// Set the session name.
	session_name($config['cookies']['session']);
	
	// Set session parameters
	session_set_cookie_params(0, $config['cookies']['jail']?$config['root']:'/');
	
	// Start the session
	session_start();
	
	// Session creation time
	if(!isset($_SESSION['created'])) $_SESSION['created'] = time();
	
	if(!isset($_COOKIE[$config['cookies']['hash']]) || !isset($_COOKIE[$config['cookies']['time']]) || $_COOKIE[$config['cookies']['hash']] != md5($_COOKIE[$config['cookies']['time']] . $config['cookies']['salt'])) {
		$time = time();
		setcookie($config['cookies']['time'], $time, time()+$config['cookies']['expire'], $config['cookies']['jail']?$config['cookies']['path']:'/', null, false, true);
		setcookie($config['cookies']['hash'], md5($time . $config['cookies']['salt']), $time+$config['cookies']['expire'], $config['cookies']['jail']?$config['cookies']['path']:'/', null, false, true);
		$user = Array('valid' => false, 'appeared' => $time);
	} else {
		$user = Array('valid' => true, 'appeared' => $_COOKIE[$config['cookies']['time']]);
	}
	
?>