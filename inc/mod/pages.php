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
		'hide_dashboard_link' => $template == 'mod/dashboard.html',
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
			modLog('Logged in');
			
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
	
	modLog("Removed a note for <a href=\"?/IP/{$ip}\">{$ip}</a>");
	
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
		
		modLog("Added a note for <a href=\"?/IP/{$ip}\">{$ip}</a>");
		
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
		
		while ($post = $query->fetch(PDO::FETCH_ASSOC)) {
			if (!$post['thread']) {
				// TODO: There is no reason why this should be such a fucking mess.
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
	
	require_once 'inc/mod/ban.php';
	
	ban($_POST['ip'], $_POST['reason'], parse_time($_POST['length']), $_POST['board'] == '*' ? false : $_POST['board']);
	
	if (isset($_POST['redirect']))
		header('Location: ' . $_POST['redirect'], true, $config['redirect_http']);
	else
		header('Location: ?/', true, $config['redirect_http']);
}

function mod_bans($page_no = 1) {
	global $config;
	
	if (!hasPermission($config['mod']['view_banlist']))
		error($config['error']['noaccess']);
	
	if (isset($_POST['unban'])) {
		if (!hasPermission($config['mod']['unban']))
			error($config['error']['noaccess']);
		
		$unban = array();
		foreach ($_POST as $name => $unused) {
			if (preg_match('/^ban_(\d+)$/', $name, $match))
				$unban[] = $match[1];
		}
		
		if (!empty($unban)) {
			query('DELETE FROM `bans` WHERE `id` = ' . implode(' OR `id` = ', $unban)) or error(db_error());
		
			foreach ($unban as $id) {
				modLog("Removed ban #{$id}");
			}
		}
		
		header('Location: ?/bans', true, $config['redirect_http']);
	}
	
	if ($config['mod']['view_banexpired']) {
		$query = prepare("SELECT `bans`.*, `username` FROM `bans` LEFT JOIN `mods` ON `mod` = `mods`.`id` ORDER BY (`expires` IS NOT NULL AND `expires` < :time), `set` DESC LIMIT :offset, :limit");
	} else {
		// Filter out expired bans
		$query = prepare("SELECT `bans`.*, `username` FROM `bans` INNER JOIN `mods` ON `mod` = `mods`.`id` WHERE `expires` = 0 OR `expires` > :time ORDER BY `set` DESC LIMIT :offset, :limit");
	}
	$query->bindValue(':time', time(), PDO::PARAM_INT);
	$query->bindValue(':limit', $config['mod']['banlist_page'], PDO::PARAM_INT);
	$query->bindValue(':offset', ($page_no - 1) * $config['mod']['banlist_page'], PDO::PARAM_INT);
	$query->execute() or error(db_error($query));
	$bans = $query->fetchAll(PDO::FETCH_ASSOC);
	
	$query = prepare("SELECT COUNT(*) FROM `bans`");
	$query->execute() or error(db_error($query));
	$count = $query->fetchColumn(0);
	
	foreach ($bans as &$ban) {
		if (filter_var($ban['ip'], FILTER_VALIDATE_IP) !== false)
			$ban['real_ip'] = true;
	}
	
	mod_page('Ban list', 'mod/ban_list.html', array('bans' => $bans, 'count' => $count));
}


function mod_lock($board, $unlock, $post) {
	global $config;
	
	if (!openBoard($board))
		error($config['error']['noboard']);
	
	if (!hasPermission($config['mod']['lock'], $board))
		error($config['error']['noaccess']);
	
	$query = prepare(sprintf('UPDATE `posts_%s` SET `locked` = :locked WHERE `id` = :id AND `thread` IS NULL', $board));
	$query->bindValue(':id', $post);
	$query->bindValue(':locked', $unlock ? 0 : 1);
	$query->execute() or error(db_error($query));
	if($query->rowCount()) {
		modLog(($unlock ? 'Unlocked' : 'Locked') . " thread #{$post}");
		buildThread($post);
		buildIndex();
	}
	
	header('Location: ?/' . sprintf($config['board_path'], $board) . $config['file_index'], true, $config['redirect_http']);
}

function mod_sticky($board, $unsticky, $post) {
	global $config;
	
	if (!openBoard($board))
		error($config['error']['noboard']);
	
	if (!hasPermission($config['mod']['sticky'], $board))
		error($config['error']['noaccess']);
	
	$query = prepare(sprintf('UPDATE `posts_%s` SET `sticky` = :sticky WHERE `id` = :id AND `thread` IS NULL', $board));
	$query->bindValue(':id', $post);
	$query->bindValue(':sticky', $unsticky ? 0 : 1);
	$query->execute() or error(db_error($query));
	if($query->rowCount()) {
		modLog(($unlock ? 'Unstickied' : 'Stickied') . " thread #{$post}");
		buildThread($post);
		buildIndex();
	}
	
	header('Location: ?/' . sprintf($config['board_path'], $board) . $config['file_index'], true, $config['redirect_http']);
}

function mod_bumplock($board, $unbumplock, $post) {
	global $config;
	
	if (!openBoard($board))
		error($config['error']['noboard']);
	
	if (!hasPermission($config['mod']['bumplock'], $board))
		error($config['error']['noaccess']);
	
	$query = prepare(sprintf('UPDATE `posts_%s` SET `sage` = :bumplock WHERE `id` = :id AND `thread` IS NULL', $board));
	$query->bindValue(':id', $post);
	$query->bindValue(':bumplock', $unbumplock ? 0 : 1);
	$query->execute() or error(db_error($query));
	if($query->rowCount()) {
		modLog(($unlock ? 'Unbumplocked' : 'Bumplocked') . " thread #{$post}");
		buildThread($post);
		buildIndex();
	}
	
	header('Location: ?/' . sprintf($config['board_path'], $board) . $config['file_index'], true, $config['redirect_http']);
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

function mod_user($uid) {
	global $config, $mod;
	
	if (!hasPermission($config['mod']['editusers']) && !(hasPermission($config['mod']['change_password']) && $uid == $mod['id']))
		error($config['error']['noaccess']);
	
	$query = prepare('SELECT * FROM `mods` WHERE `id` = :id');
	$query->bindValue(':id', $uid);
	$query->execute() or error(db_error($query));
	if (!$user = $query->fetch(PDO::FETCH_ASSOC))
		error($config['error']['404']);
	
	if (hasPermission($config['mod']['editusers']) && isset($_POST['username'], $_POST['password'])) {
		if (isset($_POST['allboards'])) {
			$boards = array('*');
		} else {
			$_boards = listBoards();
			foreach ($_boards as &$board) {
				$board = $board['uri'];
			}
		
			$boards = array();
			foreach ($_POST as $name => $value) {
				if (preg_match('/^board_(\w+)$/', $name, $matches) && in_array($matches[1], $_boards))
					$boards[] = $matches[1];
			}
		}
		
		$query = prepare('UPDATE `mods` SET `username` = :username, `boards` = :boards WHERE `id` = :id');
		$query->bindValue(':id', $uid);
		$query->bindValue(':username', $_POST['username']);
		$query->bindValue(':boards', implode(',', $boards));
		$query->execute() or error(db_error($query));
		
		if ($_POST['password'] != '') {
			$query = prepare('UPDATE `mods` SET `password` = SHA1(:password) WHERE `id` = :id');
			$query->bindValue(':id', $uid);
			$query->bindValue(':password', $_POST['password']);
			$query->execute() or error(db_error($query));
			
			if ($uid == $mod['id']) {
				login($_POST['username'], $_POST['password']);
				setCookies();
			}
		}
		
		header('Location: ?/users', true, $config['redirect_http']);
		return;
	}
	
	if (hasPermission($config['mod']['change_password']) && $uid == $mod['id'] && isset($_POST['password'])) {
		if ($_POST['password'] != '') {
			$query = prepare('UPDATE `mods` SET `password` = SHA1(:password) WHERE `id` = :id');
			$query->bindValue(':id', $uid);
			$query->bindValue(':password', $_POST['password']);
			$query->execute() or error(db_error($query));
			
			login($_POST['username'], $_POST['password']);
			setCookies();
		}
		
		header('Location: ?/users', true, $config['redirect_http']);
		return;
	}
	
	if (hasPermission($config['mod']['modlog'])) {
		$query = prepare('SELECT * FROM `modlogs` WHERE `mod` = :id ORDER BY `time` DESC LIMIT 5');
		$query->bindValue(':id', $uid);
		$query->execute() or error(db_error($query));
		$log = $query->fetchAll(PDO::FETCH_ASSOC);
	} else {
		$log = array();
	}
	
	$user['boards'] = explode(',', $user['boards']);
	
	mod_page('Edit user', 'mod/user.html', array('user' => $user, 'logs' => $log, 'boards' => listBoards()));
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
	
	modLog(($action == 'promote' ? 'Promoted' : 'Demoted') . " user #{$uid}");
	
	header('Location: ?/users', true, $config['redirect_http']);
}

function mod_pm($id, $reply = false) {
	global $mod, $config;
	
	$query = prepare("SELECT `mods`.`username`, `mods_to`.`username` AS `to_username`, `pms`.* FROM `pms` LEFT JOIN `mods` ON `mods`.`id` = `sender` LEFT JOIN `mods` AS `mods_to` ON `mods_to`.`id` = `to` WHERE `pms`.`id` = :id");
	$query->bindValue(':id', $id);
	$query->execute() or error(db_error($query));
	
	if ((!$pm = $query->fetch(PDO::FETCH_ASSOC)) || ($pm['to'] != $mod['id'] && !hasPermission($config['mod']['master_pm'])))
		error($config['error']['404']);
	
	if (isset($_POST['delete'])) {
		$query = prepare("DELETE FROM `pms` WHERE `id` = :id");
		$query->bindValue(':id', $id);
		$query->execute() or error(db_error($query));
		
		header('Location: ?/', true, $config['redirect_http']);
		return;
	}
	
	if ($pm['unread'] && $pm['to'] == $mod['id']) {
		$query = prepare("UPDATE `pms` SET `unread` = 0 WHERE `id` = :id");
		$query->bindValue(':id', $id);
		$query->execute() or error(db_error($query));
		
		modLog('Read a PM');
	}
	
	if ($reply) {
		if (!$pm['to_username'])
			error($config['error']['404']); // deleted?
		
		mod_page("New PM for {$pm['to_username']}", 'mod/new_pm.html', array('username' => $pm['to_username'], 'id' => $pm['to'], 'message' => quote($pm['message'])));
	} else {
		mod_page("Private message &ndash; #$id", 'mod/pm.html', $pm);
	}
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
		
		modLog('Sent a PM to ' . utf8tohtml($username));
		
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
			if (!(isset($_POST['boards_all']) || isset($_POST['board_' . $board['uri']])))
				continue;
			
			openBoard($board['uri']);
			
			if (isset($_POST['rebuild_index'])) {
				buildIndex();
				$log[] = '<strong>' . sprintf($config['board_abbreviation'], $board['uri']) . '</strong>: Creating index pages';
			}
			
			if (isset($_POST['rebuild_javascript']) && !in_array($config['file_script'], $rebuilt_scripts)) {
				$log[] = '<strong>' . sprintf($config['board_abbreviation'], $board['uri']) . '</strong>: Rebuilding <strong>' . $config['file_script'] . '</strong>';
				buildJavascript();
				$rebuilt_scripts[] = $config['file_script'];
			}
			
			if (isset($_POST['rebuild_thread'])) {
				$query = query(sprintf("SELECT `id` FROM `posts_%s` WHERE `thread` IS NULL", $board['uri'])) or error(db_error());
				while ($post = $query->fetch(PDO::FETCH_ASSOC)) {
					$log[] = '<strong>' . sprintf($config['board_abbreviation'], $board['uri']) . '</strong>: Rebuilding thread #' . $post['id'];
					buildThread($post['id']);
				}
			}
		}
		
		mod_page("Rebuild", 'mod/rebuilt.html', array('logs' => $log));
		return;
	}
	
	mod_page("Rebuild", 'mod/rebuild.html', array('boards' => listBoards()));
}

function mod_reports() {
	global $config, $mod;
	
	if (!hasPermission($config['mod']['reports']))
		error($config['error']['noaccess']);
	
	$query = prepare("SELECT * FROM `reports` ORDER BY `time` DESC LIMIT :limit");
	$query->bindValue(':limit', $config['mod']['recent_reports'], PDO::PARAM_INT);
	$query->execute() or error(db_error($query));
	$reports = $query->fetchAll(PDO::FETCH_ASSOC);
	
	$report_queries = array();
	foreach ($reports as $report) {
		if (!isset($report_queries[$report['board']]))
			$report_queries[$report['board']] = array();
		$report_queries[$report['board']][] = $report['post'];
	}
	
	$report_posts = array();
	foreach ($report_queries as $board => $posts) {
		$report_posts[$board] = array();
		
		$query = query(sprintf('SELECT * FROM `posts_%s` WHERE `id` = ' . implode(' OR `id` = ', $posts), $board)) or error(db_error());
		while ($post = $query->fetch()) {
			$report_posts[$board][$post['id']] = $post;
		}
	}
	
	$body = '';
	foreach ($reports as $report) {
		if (!isset($report_posts[$report['board']][$report['post']])) {
			// // Invalid report (post has since been deleted)
			$query = prepare("DELETE FROM `reports` WHERE `post` = :id AND `board` = :board");
			$query->bindValue(':id', $report['post'], PDO::PARAM_INT);
			$query->bindValue(':board', $report['board']);
			$query->execute() or error(db_error($query));
			continue;
		}
		
		openBoard($report['board']);
		
		$post = &$report_posts[$report['board']][$report['post']];
		
		if (!$post['thread']) {
			// Still need to fix this:
			$po = new Thread(
				$post['id'], $post['subject'], $post['email'], $post['name'], $post['trip'],
				$post['capcode'], $post['body'], $post['time'], $post['thumb'],
				$post['thumbwidth'], $post['thumbheight'], $post['file'], $post['filewidth'],
				$post['fileheight'], $post['filesize'], $post['filename'], $post['ip'], $post['sticky'],
				$post['locked'], $post['sage'], $post['embed'], '?/', $mod, false
			);
		} else {
			$po = new Post(
				$post['id'], $post['thread'], $post['subject'], $post['email'], $post['name'], $post['trip'], $post['capcode'],
				$post['body'], $post['time'], $post['thumb'], $post['thumbwidth'], $post['thumbheight'], $post['file'], $post['filewidth'],
				$post['fileheight'], $post['filesize'], $post['filename'], $post['ip'], $post['embed'], '?/', $mod
			);
		}
		
		// a little messy and inefficient
		$append_html = Element('mod/report.html', array('report' => $report, 'config' => $config, 'mod' => $mod));
		
		// Bug fix for https://github.com/savetheinternet/Tinyboard/issues/21
		$po->body = truncate($po->body, $po->link(), $config['body_truncate'] - substr_count($append_html, '<br>'));
		
		if (mb_strlen($po->body) + mb_strlen($append_html) > $config['body_truncate_char']) {
			// still too long; temporarily increase limit in the config
			$__old_body_truncate_char = $config['body_truncate_char'];
			$config['body_truncate_char'] = mb_strlen($po->body) + mb_strlen($append_html);
		}
		
		$po->body .= $append_html;
		
		$body .= $po->build(true) . '<hr>';
		
		if(isset($__old_body_truncate_char))
			$config['body_truncate_char'] = $__old_body_truncate_char;
	}
	
	mod_page("Report queue", 'mod/reports.html', array('reports' => $body));
}

function mod_report_dismiss($id, $all = false) {
	global $config;
	
	$query = prepare("SELECT `post`, `board`, `ip` FROM `reports` WHERE `id` = :id");
	$query->bindValue(':id', $id);
	$query->execute() or error(db_error($query));
	if ($report = $query->fetch(PDO::FETCH_ASSOC)) {
		$ip = $report['ip'];
		$board = $report['board'];
		$post = $report['post'];
	} else
		error($config['error']['404']);
	
	if (!$all && !hasPermission($config['mod']['report_dismiss'], $board))
		error($config['error']['noaccess']);
	
	if ($all && !hasPermission($config['mod']['report_dismiss_ip'], $board))
		error($config['error']['noaccess']);
	
	if ($all) {
		$query = prepare("DELETE FROM `reports` WHERE `ip` = :ip");
		$query->bindValue(':ip', $ip);
	} else {
		$query = prepare("DELETE FROM `reports` WHERE `id` = :id");
		$query->bindValue(':id', $id);
	}
	$query->execute() or error(db_error($query));
	
	
	if ($all)
		modLog("Dismissed all reports by <a href=\"?/IP/$ip\">$ip</a>");
	else
		modLog("Dismissed a report for post #{$id}", $board);
	
	header('Location: ?/reports', true, $config['redirect_http']);
}

function mod_debug_antispam() {
	$args = array();
	
	$query = query('SELECT COUNT(*) FROM `antispam`') or error(db_error());
	$args['total'] = number_format($query->fetchColumn(0));
	
	$query = query('SELECT COUNT(*) FROM `antispam` WHERE `expires` IS NOT NULL') or error(db_error());
	$args['expiring'] = number_format($query->fetchColumn(0));
	
	$query = query('SELECT * FROM `antispam` /* WHERE `passed` > 0 */ ORDER BY `passed` DESC LIMIT 25') or error(db_error());
	$args['top'] = $query->fetchAll(PDO::FETCH_ASSOC);
	
	mod_page("Debug: Anti-spam", 'mod/debug/antispam.html', $args);
}


