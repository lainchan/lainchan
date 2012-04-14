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

	mod_page('Login', 'mod/login.html', $args);
}

function mod_confirm($request) {
	mod_page('Confirm action', 'mod/confirm.html', array('request' => $request));
}

function mod_dashboard() {
	$args = array();
	
	$args['boards'] = listBoards();
	
	mod_page('Dashboard', 'mod/dashboard.html', $args);
}

function mod_log($page_no = 1) {
	global $config;
	
	if (!hasPermission($config['mod']['modlog']))
		error($config['error']['noaccess']);
	
	$query = prepare("SELECT `username`, `ip`, `board`, `time`, `text` FROM `modlogs` LEFT JOIN `mods` ON `mod` = `mods`.`id` ORDER BY `time` DESC LIMIT :offset, :limit");
	$query->bindValue(':limit', $config['mod']['modlog_page'], PDO::PARAM_INT);
	$query->bindValue(':offset', ($page_no - 1) * $config['mod']['modlog_page'], PDO::PARAM_INT);
	$query->execute() or error(db_error($query));
	$logs = $query->fetchAll(PDO::FETCH_ASSOC);
	
	$query = prepare("SELECT COUNT(*) AS `count` FROM `modlogs`");
	$query->execute() or error(db_error($query));
	$count = $query->fetchColumn(0);
	
	mod_page('Moderation log', 'mod/log.html', array('logs' => $logs, 'count' => $count));
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

function mod_ip_remove_note($ip, $id) {
	global $config, $mod;
	
	if (!hasPermission($config['mod']['remove_notes']))
			error($config['error']['noaccess']);
	
	if (filter_var($ip, FILTER_VALIDATE_IP) === false)
		error("Invalid IP address.");
	
	$query = prepare('DELETE FROM `ip_notes` WHERE `ip` = :ip AND `id` = :id');
	$query->bindValue(':ip', $ip);
	$query->bindValue(':id', $id);
	$query->execute() or error(db_error($query));
	
	header('Location: ?/IP/' . $ip, true, $config['redirect_http']);
}

function mod_page_ip($ip) {
	global $config, $mod;
	
	if (filter_var($ip, FILTER_VALIDATE_IP) === false)
		error("Invalid IP address.");
	
	if (isset($_POST['ban_id'], $_POST['unban'])) {
		if (!hasPermission($config['mod']['unban']))
			error($config['error']['noaccess']);
		
		require_once 'inc/mod/ban.php';
		
		unban($_POST['ban_id']);
		header('Location: ?/IP/' . $ip, true, $config['redirect_http']);
		return;
	}
	
	if (isset($_POST['note'])) {
		if (!hasPermission($config['mod']['create_notes']))
			error($config['error']['noaccess']);
		
		markup($_POST['note']);
		$query = prepare('INSERT INTO `ip_notes` VALUES (NULL, :ip, :mod, :time, :body)');
		$query->bindValue(':ip', $ip);
		$query->bindValue(':mod', $mod['id']);
		$query->bindValue(':time', time());
		$query->bindValue(':body', $_POST['note']);
		$query->execute() or error(db_error($query));
		
		header('Location: ?/IP/' . $ip, true, $config['redirect_http']);
		return;
	}
	
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
	
	
	if (hasPermission($config['mod']['view_ban'])) {
		$query = prepare("SELECT `bans`.*, `username` FROM `bans` LEFT JOIN `mods` ON `mod` = `mods`.`id` WHERE `ip` = :ip");
		$query->bindValue(':ip', $ip);
		$query->execute() or error(db_error($query));
		$args['bans'] = $query->fetchAll(PDO::FETCH_ASSOC);
	}
	
	if (hasPermission($config['mod']['view_notes'])) {
		$query = prepare("SELECT `ip_notes`.*, `username` FROM `ip_notes` LEFT JOIN `mods` ON `mod` = `mods`.`id` WHERE `ip` = :ip");
		$query->bindValue(':ip', $ip);
		$query->execute() or error(db_error($query));
		$args['notes'] = $query->fetchAll(PDO::FETCH_ASSOC);
	}
	
	mod_page("IP: $ip", 'mod/view_ip.html', $args);
}

function mod_ban() {
	global $config;
	
	if (!hasPermission($config['mod']['ban']))
		error($config['error']['noaccess']);
	
	if (!isset($_POST['ip'], $_POST['reason'], $_POST['length'], $_POST['board'])) {
		mod_page("New ban", 'mod/ban_form.html', array());
		return;
	}
	
	$query = prepare("SELECT `bans`.*, `username` FROM `bans` LEFT JOIN `mods` ON `mod` = `mods`.`id` WHERE `ip` = :ip");
	$query->bindValue(':ip', $ip);
	$query->execute() or error(db_error($query));
	$args['bans'] = $query->fetchAll(PDO::FETCH_ASSOC);
	
	$ip = $_POST['ip'];
	
	require_once 'inc/mod/ban.php';
	
	ban($_POST['ip'], $_POST['reason'], parse_time($_POST['length']), $_POST['board'] == '*' ? false : $_POST['board']);
	
	if (isset($_POST['redirect']))
		header('Location: ' . $_POST['redirect'], true, $config['redirect_http']);
	else
		header('Location: ?/', true, $config['redirect_http']);
}

function mod_delete($board, $post) {
	global $config, $mod;
	
	if (!openBoard($board))
		error($config['error']['noboard']);
	
	if (!hasPermission($config['mod']['delete'], $board))
		error($config['error']['noaccess']);
	
	// Delete post
	deletePost($post);
	// Record the action
	modLog("Deleted post #{$post}");
	// Rebuild board
	buildIndex();
	
	// Redirect
	header('Location: ?/' . sprintf($config['board_path'], $board) . $config['file_index'], true, $config['redirect_http']);
}

function mod_users() {
	global $config;
	
	if (!hasPermission($config['mod']['manageusers']))
		error($config['error']['noaccess']);
	
	$args = array();
	$query = query("SELECT *, (SELECT `time` FROM `modlogs` WHERE `mod` = `id` ORDER BY `time` DESC LIMIT 1) AS `last`, (SELECT `text` FROM `modlogs` WHERE `mod` = `id` ORDER BY `time` DESC LIMIT 1) AS `action` FROM `mods` ORDER BY `type` DESC,`id`") or error(db_error());
	$args['users'] = $query->fetchAll(PDO::FETCH_ASSOC);
	
	mod_page('Manage users', 'mod/users.html', $args);
}

function mod_user_promote($uid, $action) {
	global $config;
	
	if (!hasPermission($config['mod']['promoteusers']))
		error($config['error']['noaccess']);
	
	$query = prepare("UPDATE `mods` SET `type` = `type` " . ($action == 'promote' ? "+1 WHERE `type` < " . (int)ADMIN : "-1 WHERE `type` > " . (int)JANITOR) . " AND `id` = :id");
	$query->bindValue(':id', $uid);
	$query->execute() or error(db_error($query));
	
	header('Location: ?/users', true, $config['redirect_http']);
}

function mod_new_pm($username) {
	global $config, $mod;
	
	if (!hasPermission($config['mod']['create_pm']))
		error($config['error']['noaccess']);
	
	$query = prepare("SELECT `id` FROM `mods` WHERE `username` = :username");
	$query->bindValue(':username', $username);
	$query->execute() or error(db_error($query));
	if (!$id = $query->fetchColumn(0)) {
		// Old style ?/PM: by user ID
		$query = prepare("SELECT `username` FROM `mods` WHERE `id` = :username");
		$query->bindValue(':username', $username);
		$query->execute() or error(db_error($query));
		if ($username = $query->fetchColumn(0))
			header('Location: ?/new_PM/' . $username, true, $config['redirect_http']);
		else
			error($config['error']['404']);
	}
	
	if (isset($_POST['message'])) {
		markup($_POST['message']);
		
		$query = prepare("INSERT INTO `pms` VALUES (NULL, :me, :id, :message, :time, 1)");
		$query->bindValue(':me', $mod['id']);
		$query->bindValue(':id', $id);
		$query->bindValue(':message', $_POST['message']);
		$query->bindValue(':time', time());
		$query->execute() or error(db_error($query));
		
		header('Location: ?/', true, $config['redirect_http']);
	}
	
	mod_page("New PM for {$username}", 'mod/new_pm.html', array('username' => $username, 'id' => $id));
}

function mod_rebuild() {
	global $config, $twig;
	
	if (!hasPermission($config['mod']['rebuild']))
		error($config['error']['noaccess']);
	
	if (isset($_POST['rebuild'])) {
		$log = array();
		$boards = listBoards();
		$rebuilt_scripts = array();
		
		if (isset($_POST['rebuild_cache'])) {
			$log[] = 'Clearing template cache';
			load_twig();
			$twig->clearCacheFiles();
		}
		
		if (isset($_POST['rebuild_themes'])) {
			$log[] = 'Regenerating theme files';
			rebuildThemes('all');
		}
		
		if (isset($_POST['rebuild_javascript'])) {
			$log[] = 'Rebuilding <strong>' . $config['file_script'] . '</strong>';
			buildJavascript();
			$rebuilt_scripts[] = $config['file_script'];
		}
		
		foreach ($boards as $board) {
			if (!(isset($_POST['boards_all']) || isset($_POST['boards_' . $board['uri']])))
				continue;
			
			openBoard($board['uri']);
			$log[] = '<strong>' . sprintf($config['board_abbreviation'], $board['uri']) . '</strong>: Creating index pages';
			
			if (!in_array($config['file_script'], $rebuilt_scripts)) {
				$log[] = '<strong>' . sprintf($config['board_abbreviation'], $board['uri']) . '</strong>: Rebuilding <strong>' . $config['file_script'] . '</strong>';
				buildJavascript();
				$rebuilt_scripts[] = $config['file_script'];
			}
			
			$query = query(sprintf("SELECT `id` FROM `posts_%s` WHERE `thread` IS NULL", $board['uri'])) or error(db_error());
			while($post = $query->fetch()) {
				$log[] = '<strong>' . sprintf($config['board_abbreviation'], $board['uri']) . '</strong>: Rebuilding thread #' . $post['id'];
				buildThread($post['id']);
			}
		}
		
		mod_page("Rebuild", 'mod/rebuilt.html', array('logs' => $log));
		return;
	}
	
	mod_page("Rebuild", 'mod/rebuild.html', array('boards' => listBoards()));
}

