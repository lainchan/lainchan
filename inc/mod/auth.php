<?php

/*
 *  Copyright (c) 2010-2012 Tinyboard Development Group
 */

if (realpath($_SERVER['SCRIPT_FILENAME']) == str_replace('\\', '/', __FILE__)) {
	// You cannot request this file directly.
	exit;
}

// create a hash/salt pair for validate logins
function mkhash($username, $password, $salt = false) {
	global $config;
	
	if (!$salt) {
		// create some sort of salt for the hash
		$salt = substr(base64_encode(sha1(rand() . time(), true) . $config['cookies']['salt']), 0, 15);
		
		$generated_salt = true;
	}
	
	// generate hash (method is not important as long as it's strong)
	$hash = substr(base64_encode(md5($username . sha1($username . $password . $salt . ($config['mod']['lock_ip'] ? $_SERVER['REMOTE_ADDR'] : ''), true), true)), 0, 20);
	
	if (isset($generated_salt))
		return Array($hash, $salt);
	else
		return $hash;
}

function login($username, $password, $makehash=true) {
	global $mod;
	
	// SHA1 password
	if ($makehash) {
		$password = sha1($password);
	}
	
	$query = prepare("SELECT `id`,`type`,`boards` FROM `mods` WHERE `username` = :username AND `password` = :password LIMIT 1");
	$query->bindValue(':username', $username);
	$query->bindValue(':password', $password);
	$query->execute() or error(db_error($query));
	
	if ($user = $query->fetch()) {
		return $mod = Array(
			'id' => $user['id'],
			'type' => $user['type'],
			'username' => $username,
			'hash' => mkhash($username, $password),
			'boards' => explode(',', $user['boards'])
			);
	} else return false;
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
		time() + $config['cookies']['expire'], $config['cookies']['jail'] ? $config['cookies']['path'] : '/', null, false, true);
}

function destroyCookies() {
	global $config;
	// Delete the cookies
	setcookie($config['cookies']['mod'], 'deleted', time() - $config['cookies']['expire'], $config['cookies']['jail']?$config['cookies']['path'] : '/', null, false, true);
}

function modLog($action, $_board=null) {
	global $mod, $board, $config;
	$query = prepare("INSERT INTO `modlogs` VALUES (:id, :ip, :board, :time, :text)");
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
		destroyCookies();
		error($config['error']['malformed']);
	}
	
	$query = prepare("SELECT `id`, `type`, `boards`, `password` FROM `mods` WHERE `username` = :username LIMIT 1");
	$query->bindValue(':username', $cookie[0]);
	$query->execute() or error(db_error($query));
	$user = $query->fetch();
	
	// validate password hash
	if ($cookie[1] != mkhash($cookie[0], $user['password'], $cookie[2])) {
		// Malformed cookies
		destroyCookies();
		error($config['error']['malformed']);
	}
	
	$mod = Array(
		'id' => $user['id'],
		'type' => $user['type'],
		'username' => $cookie[0],
		'boards' => explode(',', $user['boards'])
	);
}

