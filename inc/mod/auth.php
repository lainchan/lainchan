<?php

/*
 *  Copyright (c) 2010-2013 Tinyboard Development Group
 */

defined('TINYBOARD') or exit;

// create a hash/salt pair for validate logins
function mkhash($username, $password, $salt = false) {
	global $config;
	
	if (!$salt) {
		// create some sort of salt for the hash
		$salt = substr(base64_encode(sha1(rand() . time(), true) . $config['cookies']['salt']), 0, 15);
		
		$generated_salt = true;
	}
	
	// generate hash (method is not important as long as it's strong)
	$hash = substr(base64_encode(md5($username . $config['cookies']['salt'] . sha1($username . $password . $salt . ($config['mod']['lock_ip'] ? $_SERVER['REMOTE_ADDR'] : ''), true), true)), 0, 20);
	
	if (isset($generated_salt))
		return array($hash, $salt);
	else
		return $hash;
}

function generate_salt() {
	mt_srand(microtime(true) * 100000 + memory_get_usage(true));
	return md5(uniqid(mt_rand(), true));
}

function login($username, $password, $makehash=true) {
	global $mod;
	
	// SHA1 password
	if ($makehash) {
		$password = sha1($password);
	}
	
	$query = prepare("SELECT `id`, `type`, `boards`, `password`, `salt` FROM ``mods`` WHERE `username` = :username");
	$query->bindValue(':username', $username);
	$query->execute() or error(db_error($query));
	
	if ($user = $query->fetch(PDO::FETCH_ASSOC)) {
		if ($user['password'] === hash('sha256', $user['salt'] . $password)) {
			return $mod = array(
				'id' => $user['id'],
				'type' => $user['type'],
				'username' => $username,
				'hash' => mkhash($username, $user['password']),
				'boards' => explode(',', $user['boards'])
			);
		}
	}
	
	return false;
}

function setCookies() {
	global $mod, $config;
	if (!$mod)
		error('setCookies() was called for a non-moderator!');
	
	setcookie($config['cookies']['mod'],
			$mod['username'] . // username
			':' . 
			$mod['hash'][0] . // password
			':' .
			$mod['hash'][1], // salt
		time() + $config['cookies']['expire'], $config['cookies']['jail'] ? $config['cookies']['path'] : '/', null, false, $config['cookies']['httponly']);
}

function destroyCookies() {
	global $config;
	// Delete the cookies
	setcookie($config['cookies']['mod'], 'deleted', time() - $config['cookies']['expire'], $config['cookies']['jail']?$config['cookies']['path'] : '/', null, false, true);
}

function modLog($action, $_board=null) {
	global $mod, $board, $config;
	$query = prepare("INSERT INTO ``modlogs`` VALUES (:id, :ip, :board, :time, :text)");
	$query->bindValue(':id', $mod['id'], PDO::PARAM_INT);
	$query->bindValue(':ip', $_SERVER['REMOTE_ADDR']);
	$query->bindValue(':time', time(), PDO::PARAM_INT);
	$query->bindValue(':text', $action);
	if (isset($_board))
		$query->bindValue(':board', $_board);
	elseif (isset($board))
		$query->bindValue(':board', $board['uri']);
	else
		$query->bindValue(':board', null, PDO::PARAM_NULL);
	$query->execute() or error(db_error($query));
	
	if ($config['syslog'])
		_syslog(LOG_INFO, '[mod/' . $mod['username'] . ']: ' . $action);
}

// Validate session

if (isset($_COOKIE[$config['cookies']['mod']])) {
	// Should be username:hash:salt
	$cookie = explode(':', $_COOKIE[$config['cookies']['mod']]);
	if (count($cookie) != 3) {
		// Malformed cookies
		destroyCookies();
		mod_login();
		exit;
	}
	
	$query = prepare("SELECT `id`, `type`, `boards`, `password` FROM ``mods`` WHERE `username` = :username");
	$query->bindValue(':username', $cookie[0]);
	$query->execute() or error(db_error($query));
	$user = $query->fetch(PDO::FETCH_ASSOC);
	
	// validate password hash
	if ($cookie[1] !== mkhash($cookie[0], $user['password'], $cookie[2])) {
		// Malformed cookies
		destroyCookies();
		mod_login();
		exit;
	}
	
	$mod = array(
		'id' => $user['id'],
		'type' => $user['type'],
		'username' => $cookie[0],
		'boards' => explode(',', $user['boards'])
	);
}

function create_pm_header() {
	global $mod, $config;
	
	if ($config['cache']['enabled'] && ($header = cache::get('pm_unread_' . $mod['id'])) != false) {
		if ($header === true)
			return false;
	
		return $header;
	}
	
	$query = prepare("SELECT `id` FROM ``pms`` WHERE `to` = :id AND `unread` = 1");
	$query->bindValue(':id', $mod['id'], PDO::PARAM_INT);
	$query->execute() or error(db_error($query));
	
	if ($pm = $query->fetch(PDO::FETCH_ASSOC))
		$header = array('id' => $pm['id'], 'waiting' => $query->rowCount() - 1);
	else
		$header = true;
	
	if ($config['cache']['enabled'])
		cache::set('pm_unread_' . $mod['id'], $header);
	
	if ($header === true)
		return false;
	
	return $header;
}

function make_secure_link_token($uri) {
	global $mod, $config;
	return substr(sha1($config['cookies']['salt'] . '-' . $uri . '-' . $mod['id']), 0, 8);
}


