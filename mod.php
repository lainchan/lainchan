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
	''					=> ':?/',		// redirect to dashboard
	'/'					=> 'dashboard',		// dashboard
	'/confirm/(.+)'				=> 'confirm',		// confirm action (if javascript didn't work)
	'/logout'				=> 'logout',		// logout
	
	'/users'				=> 'users',		// manage users
	'/users/(\d+)'				=> 'user',		// edit user
	'/users/(\d+)/(promote|demote)'		=> 'user_promote',	// prmote/demote user
	'/users/new'				=> 'user_new',		// create a new user
	'/new_PM/([^/]+)'			=> 'new_pm',		// create a new pm
	'/PM/(\d+)(/reply)?'			=> 'pm',		// read a pm
	'/inbox'				=> 'inbox',		// pm inbox
	
	'/noticeboard'				=> 'noticeboard',	// view noticeboard
	'/noticeboard/(\d+)'			=> 'noticeboard',	// view noticeboard
	'/noticeboard/delete/(\d+)'		=> 'noticeboard_delete',// delete from noticeboard
	'/log'					=> 'log',		// modlog
	'/log/(\d+)'				=> 'log',		// modlog
	'/news'					=> 'news',		// view news
	'/news/(\d+)'				=> 'news',		// view news
	'/news/delete/(\d+)'			=> 'news_delete',	// delete from news
	
	'/edit/(\w+)'				=> 'edit_board',	// edit board details
	'/new-board'				=> 'new_board',		// create a new board
	
	'/rebuild'				=> 'rebuild',		// rebuild static files
	'/reports'				=> 'reports',		// report queue
	'/reports/(\d+)/dismiss(all)?'		=> 'report_dismiss',	// dismiss a report
	
	'/ban'					=> 'ban',		// new ban
	'/IP/([\w.:]+)'				=> 'ip',		// view ip address
	'/IP/([\w.:]+)/remove_note/(\d+)'	=> 'ip_remove_note',	// remove note from ip address
	'/bans'					=> 'bans',		// ban list
	'/bans/(\d+)'				=> 'bans',		// ban list
	
	'/(\w+)/delete/(\d+)'			=> 'delete',		// delete post
	'/(\w+)/ban(&delete)?/(\d+)'		=> 'ban_post',		// ban poster
	'/(\w+)/deletefile/(\d+)'		=> 'deletefile',	// delete file from post
	'/(\w+)/deletebyip/(\d+)(/global)?'	=> 'deletebyip',	// delete all posts by IP address
	'/(\w+)/(un)?lock/(\d+)'		=> 'lock',		// lock thread
	'/(\w+)/(un)?sticky/(\d+)'		=> 'sticky',		// sticky thread
	'/(\w+)/bump(un)?lock/(\d+)'		=> 'bumplock',		// "bumplock" thread
	'/(\w+)/move/(\d+)'			=> 'move',		// move thread
	
	// these pages aren't listed in the dashboard without $config['debug']
	'/debug/antispam'			=> 'debug_antispam',
	
	// This should always be at the end:
	'/(\w+)/'										=> 'view_board',
	'/(\w+)/' . preg_quote($config['file_index'], '!')					=> 'view_board',
	'/(\w+)/' . str_replace('%d', '(\d+)', preg_quote($config['file_page'], '!'))		=> 'view_board',
	'/(\w+)/' . preg_quote($config['dir']['res'], '!') .
			str_replace('%d', '(\d+)', preg_quote($config['file_page'], '!'))	=> 'view_thread',
);



if (!$mod) {
	$pages = array('!!' => 'login');
} elseif (isset($_GET['status'], $_GET['r'])) {
	header('Location: ' . $_GET['r'], true, (int)$_GET['status']);
	exit;
}

if (isset($config['mod']['custom_pages'])) {
	$pages = array_merge($pages, $config['mod']['custom_pages']);
}

$new_pages = array();
foreach ($pages as $key => $callback) {
	$new_pages[@$key[0] == '!' ? $key : "!^$key$!"] = $callback;
}
$pages = $new_pages;

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
		
		if (is_string($handler)) {
			if ($handler[0] == ':') {
				header('Location: ' . substr($handler, 1),  true, $config['redirect_http']);
			} elseif (is_callable("mod_page_$handler")) {
				call_user_func_array("mod_page_$handler", $matches);
			} elseif (is_callable("mod_$handler")) {
				call_user_func_array("mod_$handler", $matches);
			} else {
				error("Mod page '$handler' not found!");
			}
		} elseif (is_callable($handler)) {
			call_user_func_array($handler, $matches);
		} else {
			error("Mod page '$handler' not a string, and not callable!");
		}
		
		exit;
	}
}

error($config['error']['404']);

