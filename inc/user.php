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
		setcookie(HASH_COOKIE, md5($time.SALT), $time+COOKIE_EXPIRE, JAIL_COOKIES?ROOT:'/', null, false, true);
		$user = Array('valid' => false, 'appeared' => $time);
	} else {
		$user = Array('valid' => true, 'appeared' => $_COOKIE[TIME_COOKIE]);
	}
	
	// 'false' means that the user is not logged in as a moderator
	$mod = false;
	
	// Creates a small random string for validating moderators' cookies
	function mkhash($length=12) {
		// The method here isn't really important,
		// but I think this generates a relatively
		// unique string that looks cool.
		// If you choose to change this, make sure it cannot include a ':' character.
		return substr(base64_encode(sha1(rand() . time(), true)), 0, $length);
	}
	
	function login($username, $password, $makehash=true) {
		global $sql, $mod;
		
		// SHA1 password
		if($makehash) {
			$password = sha1($password);
		}
		
		$res = mysql_query(sprintf(
			"SELECT `id`,`type` FROM `mods` WHERE `username` = '%s' AND `password` = '%s' LIMIT 1",
				mysql_real_escape_string($username),
				$password
		), $sql) or error(mysql_error($sql));
		
		if($user = mysql_fetch_array($res)) {
			return $mod = Array(
				'id' => $user['id'],
				'type' => $user['type'],
				'username' => $username,
				'password' => $password,
				'hash' => mkhash()
				);
		} else return false;
	}
	
	function setCookies() {
		global $mod;
		if(!$mod) error('setCookies() was called for a non-moderator!');
		
		// MOD_COOKIE contains username:hash
		setcookie(MOD_COOKIE, $mod['username'] . ':' . $mod['hash'], time()+COOKIE_EXPIRE, JAIL_COOKIES?ROOT:'/', null, false, true);
		
		// Put $mod in the session
		$_SESSION['mod'] = $mod;
		
		// Lock sessions to IP addresses
		if(MOD_LOCK_IP)
			$_SESSION['mod']['ip'] = $_SERVER['REMOTE_ADDR'];
	}
	
	function destroyCookies() {
		// Delete the cookies
		setcookie(MOD_COOKIE, 'deleted', time()-COOKIE_EXPIRE, JAIL_COOKIES?ROOT:'/', null, false, true);
		
		// Unset the session
		unset($_SESSION['mod']);
	}
	
	if(isset($_COOKIE['mod']) && isset($_SESSION['mod']) && is_array($_SESSION['mod'])) {
		// Should be username:session hash
		$cookie = explode(':', $_COOKIE['mod']);
		if(count($cookie) != 2) {
			destroyCookies();
			error(ERROR_MALFORMED);
		}
		
		// Validate session
		if(	$cookie[0] != $_SESSION['mod']['username'] ||
			$cookie[1] != $_SESSION['mod']['hash']) {
			// Malformed cookies
			destroyCookies();
			error(ERROR_MALFORMED);
		}
		
		// Open connection
		sql_open();
		
		// Check username/password
		if(!login($_SESSION['mod']['username'], $_SESSION['mod']['password'], false)) {
			destroyCookies();
			error(ERROR_INVALIDAFTER);
		}
		
		$mod = $_SESSION['mod'];
	}
?>