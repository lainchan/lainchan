<?php

/*
 *  Copyright (c) 2010-2012 Tinyboard Development Group
 */

require 'inc/functions.php';
require 'inc/mod/auth.php';
require 'inc/mod/pages.php';

// Fix for magic quotes
if (get_magic_quotes_gpc()) {
	function strip_array($var) {
		return is_array($var) ? array_map('strip_array', $var) : stripslashes($var);
	}
	
	$_GET = strip_array($_GET);
	$_POST = strip_array($_POST);
}

$query = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';

$pages = array(
	'!^$!'		=> ':?/',		// redirect to dashboard
	'!^/$!'		=> 'dashboard',		// dashboard
	
	'!^/IP/(.+)$!'	=> 'ip',		// view ip address
	
	// This should always be at the end:
	'!^/(\w+)/' . preg_quote($config['file_index'], '!') . '?$!'					=> 'view_board',
	'!^/(\w+)/' . str_replace('%d', '(\d+)', preg_quote($config['file_page'], '!')) . '$!/'	=> 'view_board',
	'!^/(\w+)/' . preg_quote($config['dir']['res'], '!') .
			str_replace('%d', '(\d+)', preg_quote($config['file_page'], '!')) . '$!'	=> 'view_thread',
);

if (!$mod)
	$pages = array('//' => 'login');

foreach ($pages as $uri => $handler) {
	if (preg_match($uri, $query, $matches)) {
		$matches = array_slice($matches, 1);
		
		if ($config['debug']) {
			$debug['mod_page'] = array(
				'req' => $query,
				'match' => $uri,
				'handler' => $handler
			);
		}
		
		if ($handler[0] == ':') {
			header('Location: ' . substr($handler, 1),  true, $config['redirect_http']);
		} elseif (is_callable("mod_page_$handler")) {
			call_user_func_array("mod_page_$handler", $matches);
		} elseif (is_callable("mod_$handler")) {
			call_user_func_array("mod_$handler", $matches);
		} else {
			error("Mod page '$handler' not found!");
		}
		
		exit;
	}
}

error($config['error']['404']);

