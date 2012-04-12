<?php

/*
 *  Copyright (c) 2010-2012 Tinyboard Development Group
 */

if (realpath($_SERVER['SCRIPT_FILENAME']) == str_replace('\\', '/', __FILE__)) {
	// You cannot request this file directly.
	exit;
}

function mod_page($title, $template, $args) {
	global $config, $mod;
	
	echo Element('page.html', array(
		'config' => $config,
		'mod' => $mod,
		'title' => $title,
		'body' => Element($template,
				array_merge(
					array('config' => $config, 'mod' => $mod), 
					$args
				)
			)
		)
	);
}

function mod_login() {
	$args = array();
	
	if (isset($_POST['login'])) {
		// Check if inputs are set and not empty
		if (!isset($_POST['username'], $_POST['password']) || $_POST['username'] == '' || $_POST['password'] == '') {
			$args['error'] = $config['error']['invalid'];
		} elseif (!login($_POST['username'], $_POST['password'])) {
			if ($config['syslog'])
				_syslog(LOG_WARNING, 'Unauthorized login attempt!');
			
			$args['error'] = $config['error']['invalid'];
		} else {
			modLog("Logged in.");
			
			// Login successful
			// Set cookies
			setCookies();
			
			header('Location: ?/', true, $config['redirect_http']);
		}
	}
	
	if (isset($_POST['username']))
		$args['username'] = $_POST['username'];

	mod_page('Dashboard', 'mod/login.html', $args);
}

function mod_dashboard() {
	$args = array();
	
	$args['boards'] = listBoards();
	
	mod_page('Dashboard', 'mod/dashboard.html', $args);
}

function mod_view_board($boardName, $page_no = 1) {
	global $config, $mod;
	
	if (!openBoard($boardName))
		error($config['error']['noboard']);
	
	if (!$page = index($page_no, $mod)) {
		error($config['error']['404']);
	}
	
	$page['pages'] = getPages(true);
	$page['pages'][$page_no-1]['selected'] = true;
	$page['btn'] = getPageButtons($page['pages'], true);
	$page['mod'] = true;
	$page['config'] = $config;
	echo Element('index.html', $page);
}

function mod_view_thread($boardName, $thread) {
	global $config, $mod;
	
	if (!openBoard($boardName))
		error($config['error']['noboard']);
	
	$page = buildThread($thread, true, $mod);
	echo $page;
}

function mod_page_ip($ip) {
	global $config, $mod;
	
	if(filter_var($ip, FILTER_VALIDATE_IP) === false)
		error("Invalid IP address.");
	
	$args = array();
	$args['ip'] = $ip;
	$args['posts'] = array();
	
	$boards = listBoards();
	foreach ($boards as $board) {
		openBoard($board['uri']);
		
		$query = prepare(sprintf('SELECT * FROM `posts_%s` WHERE `ip` = :ip ORDER BY `sticky` DESC, `id` DESC LIMIT :limit', $board['uri']));
		$query->bindValue(':ip', $ip);
		$query->bindValue(':limit', $config['mod']['ip_recentposts'], PDO::PARAM_INT);
		$query->execute() or error(db_error($query));
		
		while ($post = $query->fetch()) {
			if (!$post['thread']) {
				$po = new Thread(
					$post['id'], $post['subject'], $post['email'], $post['name'], $post['trip'], $post['capcode'], $post['body'],
					$post['time'], $post['thumb'], $post['thumbwidth'], $post['thumbheight'], $post['file'], $post['filewidth'],
					$post['fileheight'], $post['filesize'], $post['filename'], $post['ip'], $post['sticky'], $post['locked'],
					$post['sage'], $post['embed'], '?/', $mod, false
				);
			} else {
				$po = new Post(
					$post['id'], $post['thread'], $post['subject'], $post['email'], $post['name'], $post['trip'], $post['capcode'],
					$post['body'], $post['time'], $post['thumb'], $post['thumbwidth'], $post['thumbheight'], $post['file'], $post['filewidth'],
					$post['fileheight'], $post['filesize'], $post['filename'], $post['ip'],  $post['embed'], '?/', $mod
				);
			}
			
			if (!isset($args['posts'][$board['uri']]))
				$args['posts'][$board['uri']] = array('board' => $board, 'posts' => array());
			$args['posts'][$board['uri']]['posts'][] = $po->build(true);
		}
	}
	
	$args['boards'] = $boards;
	
	mod_page("IP: $ip", 'mod/view_ip.html', $args);
}

function mod_page_ban() {
	if(!isset($_POST['ip'], $_POST['reason'], $_POST['length'], $_POST['board']))
		error($config['error']['missedafield']);
	
	$ip = $_POST['ip'];
	
	require_once 'inc/mod/ban.php';
	
	ban($_POST['ip'], $_POST['reason'], parse_time($_POST['length']), $_POST['board'] == '*' ? false : $_POST['board']);
	
	
	if(isset($_POST['redirect']))
		header('Location: ' . $_POST['redirect'], true, $config['redirect_http']);
	else
		header('Location: ?/', true, $config['redirect_http']);
}

