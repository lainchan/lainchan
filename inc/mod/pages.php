<?php

/*
 *  Copyright (c) 2010-2012 Tinyboard Development Group
 */

if (realpath($_SERVER['SCRIPT_FILENAME']) == str_replace('\\', '/', __FILE__)) {
	// You cannot request this file directly.
	exit;
}

function mod_page($title, $template, $args, $subtitle = false) {
	global $config, $mod;
	
	echo Element('page.html', array(
		'config' => $config,
		'mod' => $mod,
		'hide_dashboard_link' => $template == 'mod/dashboard.html',
		'title' => $title,
		'subtitle' => $subtitle,
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
	global $config;
	
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

function mod_logout() {
	destroyCookies();
	
	header('Location: ?/', true, $config['redirect_http']);
}

function mod_dashboard() {
	global $config, $mod;
	
	$args = array();
	
	$args['boards'] = listBoards();
	
	if (hasPermission($config['mod']['noticeboard'])) {
		if (!$config['cache']['enabled'] || !$args['noticeboard'] = cache::get('noticeboard_preview')) {
			$query = prepare("SELECT `noticeboard`.*, `username` FROM `noticeboard` LEFT JOIN `mods` ON `mods`.`id` = `mod` ORDER BY `id` DESC LIMIT :limit");
			$query->bindValue(':limit', $config['mod']['noticeboard_dashboard'], PDO::PARAM_INT);
			$query->execute() or error(db_error($query));
			$args['noticeboard'] = $query->fetchAll(PDO::FETCH_ASSOC);
			
			if ($config['cache']['enabled'])
				cache::set('noticeboard_preview', $args['noticeboard']);
		}
	}
	
	$query = prepare('SELECT COUNT(*) FROM `pms` WHERE `to` = :id AND `unread` = 1');
	$query->bindValue(':id', $mod['id']);
	$query->execute() or error(db_error($query));
	$args['unread_pms'] = $query->fetchColumn(0);
	
	if ($mod['type'] >= ADMIN && $config['check_updates']) {
		if (!$config['version'])
			error(_('Could not find current version! (Check .installed)'));
		
		if (isset($_COOKIE['update'])) {
			$latest = unserialize($_COOKIE['update']);
		} else {
			$ctx = stream_context_create(array('http' => array('timeout' => 5)));
			if ($code = @file_get_contents('http://tinyboard.org/version.txt', 0, $ctx)) {
				eval($code);
				if (preg_match('/v(\d+)\.(\d)\.(\d+)(-dev.+)?$/', $config['version'], $matches)) {
					$current = array(
						'massive' => (int) $matches[1],
						'major' => (int) $matches[2],
						'minor' => (int) $matches[3]
					);
					if (isset($m[4])) { 
						// Development versions are always ahead in the versioning numbers
						$current['minor'] --;
					}
					// Check if it's newer
					if (!(	$latest['massive'] > $current['massive'] ||
						$latest['major'] > $current['major'] ||
							($latest['massive'] == $current['massive'] &&
								$latest['major'] == $current['major'] &&
								$latest['minor'] > $current['minor']
							)))
						$latest = false;
				} else {
					$latest = false;
				}
			} else {
				// Couldn't get latest version
				$latest = false;
			}
	
			setcookie('update', serialize($latest), time() + $config['check_updates_time'], $config['cookies']['jail'] ? $config['cookies']['path'] : '/', null, false, true);
		}
		
		if ($latest)
			$args['newer_release'] = $latest;
	}
			
	mod_page('Dashboard', 'mod/dashboard.html', $args);
}

function mod_edit_board($boardName) {
	global $board, $config;
	
	if (!openBoard($boardName))
		error($config['error']['noboard']);
	
	if (!hasPermission($config['mod']['manageboards'], $board['uri']))
			error($config['error']['noaccess']);
	
	if (isset($_POST['title'], $_POST['subtitle'])) {
		if (isset($_POST['delete'])) {
			if (!hasPermission($config['mod']['manageboards'], $board['uri']))
				error($config['error']['deleteboard']);
			
			$query = prepare('DELETE FROM `boards` WHERE `uri` = :uri');
			$query->bindValue(':uri', $board['uri']);
			$query->execute() or error(db_error($query));
			
			modLog('Deleted board: ' . sprintf($config['board_abbreviation'], $board['uri']), false);
			
			// Delete entire board directory
			rrmdir($board['uri'] . '/');
			
			// Delete posting table
			$query = query(sprintf('DROP TABLE IF EXISTS `posts_%s`', $board['uri'])) or error(db_error());
			
			// Clear reports
			$query = prepare('DELETE FROM `reports` WHERE `board` = :id');
			$query->bindValue(':id', $board['uri'], PDO::PARAM_INT);
			$query->execute() or error(db_error($query));
			
			// Delete from table
			$query = prepare('DELETE FROM `boards` WHERE `uri` = :uri');
			$query->bindValue(':uri', $board['uri'], PDO::PARAM_INT);
			$query->execute() or error(db_error($query));
			
			if ($config['cache']['enabled']) {
				cache::delete('board_' . $board['uri']);
				cache::delete('all_boards');
			}
			
			$query = prepare("SELECT `board`, `post` FROM `cites` WHERE `target_board` = :board");
			$query->bindValue(':board', $board['uri']);
			$query->execute() or error(db_error($query));
			while ($cite = $query->fetch(PDO::FETCH_ASSOC)) {
				if ($board['uri'] != $cite['board']) {
					if (!isset($tmp_board))
						$tmp_board = $board;
					openBoard($cite['board']);
					rebuildPost($cite['post']);
				}
			}
			
			$query = prepare('DELETE FROM `cites` WHERE `board` = :board OR `target_board` = :board');
			$query->bindValue(':board', $board['uri']);
			$query->execute() or error(db_error($query));
			
			$query = prepare('DELETE FROM `antispam` WHERE `board` = :board');
			$query->bindValue(':board', $board['uri']);
			$query->execute() or error(db_error($query));
		} else {
			$query = prepare('UPDATE `boards` SET `title` = :title, `subtitle` = :subtitle WHERE `uri` = :uri');
			$query->bindValue(':uri', $board['uri']);
			$query->bindValue(':title', $_POST['title']);
			$query->bindValue(':subtitle', $_POST['subtitle']);
			$query->execute() or error(db_error($query));
		}
		
		rebuildThemes('boards');
		
		header('Location: ?/', true, $config['redirect_http']);
	} else {
		mod_page('Edit board: ' . sprintf($config['board_abbreviation'], $board['uri']), 'mod/board.html', array('board' => $board));
	}
}

function mod_new_board() {
	global $config, $board;
	
	if (!hasPermission($config['mod']['newboard']))
		error($config['error']['noaccess']);
	
	if (isset($_POST['uri'], $_POST['title'], $_POST['subtitle'])) {
		if ($_POST['uri'] == '')
			error(sprintf($config['error']['required'], 'URI'));
		
		if ($_POST['title'] == '')
			error(sprintf($config['error']['required'], 'title'));
		
		if (!preg_match('/^\w+$/', $_POST['uri']))
			error(sprintf($config['error']['invalidfield'], 'URI'));
		
		if (openBoard($_POST['uri'])) {
			error(sprintf($config['error']['boardexists'], $board['url']));
		}
		
		$query = prepare('INSERT INTO `boards` VALUES (:uri, :title, :subtitle)');
		$query->bindValue(':uri', $_POST['uri']);
		$query->bindValue(':title', $_POST['title']);
		$query->bindValue(':subtitle', $_POST['subtitle']);
		$query->execute() or error(db_error($query));
		
		modLog('Created a new board: ' . sprintf($config['board_abbreviation'], $_POST['uri']));
		
		if (!openBoard($_POST['uri']))
			error(_("Couldn't open board after creation."));
		
		query(Element('posts.sql', array('board' => $board['uri']))) or error(db_error());
		
		if ($config['cache']['enabled'])
				cache::delete('all_boards');
		
		// Build the board
		buildIndex();
		
		rebuildThemes('boards');
		
		header('Location: ?/' . $board['uri'] . '/' . $config['file_index'], true, $config['redirect_http']);
	}
	
	mod_page('New board', 'mod/board.html', array('new' => true));
}

function mod_noticeboard($page_no = 1) {
	global $config, $pdo, $mod;
	
	if ($page_no < 1)
		error($config['error']['404']);
	
	if (!hasPermission($config['mod']['noticeboard']))
		error($config['error']['noaccess']);
	
	if (isset($_POST['subject'], $_POST['body'])) {
		if (!hasPermission($config['mod']['noticeboard_post']))
			error($config['error']['noaccess']);
		
		markup($_POST['body']);
		
		$query = prepare('INSERT INTO `noticeboard` VALUES (NULL, :mod, :time, :subject, :body)');
		$query->bindValue(':mod', $mod['id']);
		$query->bindvalue(':time', time());
		$query->bindValue(':subject', $_POST['subject']);
		$query->bindValue(':body', $_POST['body']);
		$query->execute() or error(db_error($query));
		
		if ($config['cache']['enabled'])
			cache::delete('noticeboard_preview');
		
		modLog('Posted a noticeboard entry');
		
		header('Location: ?/noticeboard#' . $pdo->lastInsertId(), true, $config['redirect_http']);
	}
	
	$query = prepare("SELECT `noticeboard`.*, `username` FROM `noticeboard` LEFT JOIN `mods` ON `mods`.`id` = `mod` ORDER BY `id` DESC LIMIT :offset, :limit");
	$query->bindValue(':limit', $config['mod']['noticeboard_page'], PDO::PARAM_INT);
	$query->bindValue(':offset', ($page_no - 1) * $config['mod']['noticeboard_page'], PDO::PARAM_INT);
	$query->execute() or error(db_error($query));
	$noticeboard = $query->fetchAll(PDO::FETCH_ASSOC);
	
	if (empty($noticeboard) && $page_no > 1)
		error($config['error']['404']);
	
	$query = prepare("SELECT COUNT(*) FROM `noticeboard`");
	$query->execute() or error(db_error($query));
	$count = $query->fetchColumn(0);
	
	mod_page('Noticeboard', 'mod/noticeboard.html', array('noticeboard' => $noticeboard, 'count' => $count));
}

function mod_noticeboard_delete($id) {
	global $config;
	
	if (!hasPermission($config['mod']['noticeboard_delete']))
			error($config['error']['noaccess']);
	
	$query = prepare('DELETE FROM `noticeboard` WHERE `id` = :id');
	$query->bindValue(':id', $id);
	$query->execute() or error(db_error($query));
	
	modLog('Deleted a noticeboard entry');
	
	header('Location: ?/noticeboard', true, $config['redirect_http']);
}

function mod_news($page_no = 1) {
	global $config, $pdo, $mod;
	
	if ($page_no < 1)
		error($config['error']['404']);
	
	if (isset($_POST['subject'], $_POST['body'])) {
		if (!hasPermission($config['mod']['news']))
			error($config['error']['noaccess']);
		
		markup($_POST['body']);
		
		$query = prepare('INSERT INTO `news` VALUES (NULL, :name, :time, :subject, :body)');
		$query->bindValue(':name', isset($_POST['name']) && hasPermission($config['mod']['news_custom']) ? $_POST['name'] : $mod['username']);
		$query->bindvalue(':time', time());
		$query->bindValue(':subject', $_POST['subject']);
		$query->bindValue(':body', $_POST['body']);
		$query->execute() or error(db_error($query));
		
		modLog('Posted a news entry');
		
		rebuildThemes('news');
		
		header('Location: ?/news#' . $pdo->lastInsertId(), true, $config['redirect_http']);
	}
	
	$query = prepare("SELECT * FROM `news` ORDER BY `id` DESC LIMIT :offset, :limit");
	$query->bindValue(':limit', $config['mod']['news_page'], PDO::PARAM_INT);
	$query->bindValue(':offset', ($page_no - 1) * $config['mod']['news_page'], PDO::PARAM_INT);
	$query->execute() or error(db_error($query));
	$news = $query->fetchAll(PDO::FETCH_ASSOC);
	
	if (empty($news) && $page_no > 1)
		error($config['error']['404']);
	
	$query = prepare("SELECT COUNT(*) FROM `news`");
	$query->execute() or error(db_error($query));
	$count = $query->fetchColumn(0);
	
	mod_page('News', 'mod/news.html', array('news' => $news, 'count' => $count));
}

function mod_news_delete($id) {
	global $config;
	
	if (!hasPermission($config['mod']['news_delete']))
			error($config['error']['noaccess']);
	
	$query = prepare('DELETE FROM `news` WHERE `id` = :id');
	$query->bindValue(':id', $id);
	$query->execute() or error(db_error($query));
	
	modLog('Deleted a news entry');
	
	header('Location: ?/news', true, $config['redirect_http']);
}

function mod_log($page_no = 1) {
	global $config;
	
	if ($page_no < 1)
		error($config['error']['404']);
	
	if (!hasPermission($config['mod']['modlog']))
		error($config['error']['noaccess']);
	
	$query = prepare("SELECT `username`, `mod`, `ip`, `board`, `time`, `text` FROM `modlogs` LEFT JOIN `mods` ON `mod` = `mods`.`id` ORDER BY `time` DESC LIMIT :offset, :limit");
	$query->bindValue(':limit', $config['mod']['modlog_page'], PDO::PARAM_INT);
	$query->bindValue(':offset', ($page_no - 1) * $config['mod']['modlog_page'], PDO::PARAM_INT);
	$query->execute() or error(db_error($query));
	$logs = $query->fetchAll(PDO::FETCH_ASSOC);
	
	if (empty($logs) && $page_no > 1)
		error($config['error']['404']);
	
	$query = prepare("SELECT COUNT(*) FROM `modlogs`");
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
	
	header('Location: ?/IP/' . $ip . '#notes', true, $config['redirect_http']);
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
		
		header('Location: ?/IP/' . $ip . '#bans', true, $config['redirect_http']);
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
		
		header('Location: ?/IP/' . $ip . '#notes', true, $config['redirect_http']);
		return;
	}
	
	$args = array();
	$args['ip'] = $ip;
	$args['posts'] = array();
	
	if ($config['mod']['dns_lookup'])
		$args['hostname'] = rDNS($ip);
	
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
	
	mod_page("IP: $ip", 'mod/view_ip.html', $args, $args['hostname']);
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
	
	if ($page_no < 1)
		error($config['error']['404']);
	
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
	
	if (empty($bans) && $page_no > 1)
		error($config['error']['404']);
	
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
	if ($query->rowCount()) {
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
	if ($query->rowCount()) {
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
	if ($query->rowCount()) {
		modLog(($unlock ? 'Unbumplocked' : 'Bumplocked') . " thread #{$post}");
		buildThread($post);
		buildIndex();
	}
	
	header('Location: ?/' . sprintf($config['board_path'], $board) . $config['file_index'], true, $config['redirect_http']);
}

function mod_move($originBoard, $postID) {
	global $board, $config, $mod;
	
	if (!openBoard($originBoard))
		error($config['error']['noboard']);
	
	if (!hasPermission($config['mod']['move'], $originBoard))
		error($config['error']['noaccess']);
	
	$query = prepare(sprintf('SELECT * FROM `posts_%s` WHERE `id` = :id AND `thread` IS NULL', $originBoard));
	$query->bindValue(':id', $postID);
	$query->execute() or error(db_error($query));
	if (!$post = $query->fetch(PDO::FETCH_ASSOC))
		error($config['error']['404']);
	
	if (isset($_POST['board'])) {
		$targetBoard = $_POST['board'];
		$shadow = isset($_POST['shadow']);
		
		if ($targetBoard === $originBoard)
			error(_('Target and source board are the same.'));
		
		// copy() if leaving a shadow thread behind; else, rename().
		$clone = $shadow ? 'copy' : 'rename';
		
		// indicate that the post is a thread
		$post['op'] = true;
		
		if ($post['file']) {
			$post['has_file'] = true;
			$post['width'] = &$post['filewidth'];
			$post['height'] = &$post['fileheight'];
			
			$file_src = sprintf($config['board_path'], $board['uri']) . $config['dir']['img'] . $post['file'];
			$file_thumb = sprintf($config['board_path'], $board['uri']) . $config['dir']['thumb'] . $post['thumb'];
		} else {
			$post['has_file'] = false;
		}
		
		// allow thread to keep its same traits (stickied, locked, etc.)
		$post['mod'] = true;
		
		if (!openBoard($targetBoard))
			error($config['error']['noboard']);
		
		// create the new thread
		$newID = post($post);
		
		if($post['has_file']) {
			// copy image
			$clone($file_src, sprintf($config['board_path'], $board['uri']) . $config['dir']['img'] . $post['file']);
			$clone($file_thumb, sprintf($config['board_path'], $board['uri']) . $config['dir']['thumb'] . $post['thumb']);
		}
		
		// go back to the original board to fetch replies
		openBoard($originBoard);
		
		$query = prepare(sprintf('SELECT * FROM `posts_%s` WHERE `thread` = :id ORDER BY `id`', $originBoard));
		$query->bindValue(':id', $postID, PDO::PARAM_INT);
		$query->execute() or error(db_error($query));
		
		$replies = array();
		
		while($post = $query->fetch()) {
			$post['mod'] = true;
			$post['thread'] = $newID;
			
			if($post['file']) {
				$post['has_file'] = true;
				$post['width'] = &$post['filewidth'];
				$post['height'] = &$post['fileheight'];
				
				$post['file_src'] = sprintf($config['board_path'], $board['uri']) . $config['dir']['img'] . $post['file'];
				$post['file_thumb'] = sprintf($config['board_path'], $board['uri']) . $config['dir']['thumb'] . $post['thumb'];
			} else {
				$post['has_file'] = false;
			}
			
			$replies[] = $post;
		}
		
		$newIDs = array($postID => $newID);
		
		openBoard($targetBoard);
		
		foreach($replies as &$post) {
			$query = prepare('SELECT `target` FROM `cites` WHERE `target_board` = :board AND `board` = :board AND `post` = :post');
			$query->bindValue(':board', $originBoard);
			$query->bindValue(':post', $post['id'], PDO::PARAM_INT);
			$query->execute() or error(db_error($qurey));
			
			// correct >>X links
			while($cite = $query->fetch(PDO::FETCH_ASSOC)) {
				if(isset($newIDs[$cite['target']])) {
					$post['body_nomarkup'] = preg_replace(
							'/(>>(>\/' . preg_quote($originBoard, '/') . '\/)?)' . preg_quote($cite['target'], '/') . '/',
							'>>' . $newIDs[$cite['target']],
							$post['body_nomarkup']);
					
					$post['body'] = $post['body_nomarkup'];
				}
			}
			
			$post['body'] = $post['body_nomarkup'];
			
			$post['op'] = false;
			$post['tracked_cites'] = markup($post['body'], true);
			
			// insert reply
			$newIDs[$post['id']] = $newPostID = post($post);
			
			if($post['has_file']) {
				// copy image
				$clone($post['file_src'], sprintf($config['board_path'], $board['uri']) . $config['dir']['img'] . $post['file']);
				$clone($post['file_thumb'], sprintf($config['board_path'], $board['uri']) . $config['dir']['thumb'] . $post['thumb']);
			}
			
			foreach($post['tracked_cites'] as $cite) {
				$query = prepare('INSERT INTO `cites` VALUES (:board, :post, :target_board, :target)');
				$query->bindValue(':board', $board['uri']);
				$query->bindValue(':post', $newPostID, PDO::PARAM_INT);
				$query->bindValue(':target_board',$cite[0]);
				$query->bindValue(':target', $cite[1], PDO::PARAM_INT);
				$query->execute() or error(db_error($query));
			}
		}
		
		// build new hread
		buildThread($newID);
		buildIndex();
		
		// trigger themes
		rebuildThemes('post');
		
		// return to original board
		openBoard($originBoard);
		
		if($shadow) {
			// lock old thread
			$query = prepare(sprintf('UPDATE `posts_%s` SET `locked` = 1 WHERE `id` = :id', $originBoard));
			$query->bindValue(':id', $postID, PDO::PARAM_INT);
			$query->execute() or error(db_error($query));
			
			// leave a reply, linking to the new thread
			$post = array(
				'mod' => true,
				'subject' => '',
				'email' => '',
				'name' => $config['mod']['shadow_name'],
				'capcode' => $config['mod']['shadow_capcode'],
				'trip' => '',
				'password' => '',
				'has_file' => false,
				// attach to original thread
				'thread' => $postID,
				'op' => false
			);
			
			$post['body'] = $post['body_nomarkup'] =  sprintf($config['mod']['shadow_mesage'], '>>>/' . $targetBoard . '/' . $newID);
			
			markup($post['body']);
			
			$botID = post($post);
			buildThread($postID);
			
			header('Location: ?/' . sprintf($config['board_path'], $originBoard) . $config['dir']['res'] .sprintf($config['file_page'], $postID) .
				'#' . $botID, true, $config['redirect_http']);
		} else {
			deletePost($postID);
			buildIndex();
			
			openBoard($targetBoard);
			header('Location: ?/' . sprintf($config['board_path'], $board['uri']) . $config['dir']['res'] . sprintf($config['file_page'], $newID), true, $config['redirect_http']);
		}
	}
	
	$boards = listBoards();
	if (count($boards) <= 1)
		error(_('Impossible to move thread; there is only one board.'));
	
	mod_page("Move thread", 'mod/move.html', array('post' => $postID, 'board' => $originBoard, 'boards' => $boards));
}

function mod_ban_post($board, $delete, $post) {
	global $config, $mod;
	
	if (!openBoard($board))
		error($config['error']['noboard']);
	
	if (!hasPermission($config['mod']['delete'], $board))
		error($config['error']['noaccess']);
	
	$query = prepare(sprintf('SELECT `ip`, `thread` FROM `posts_%s` WHERE `id` = :id', $board));
	$query->bindValue(':id', $post);
	$query->execute() or error(db_error($query));
	if (!$_post = $query->fetch(PDO::FETCH_ASSOC))
		error($config['error']['404']);
	
	$thread = $_post['thread'];
	$ip = $_post['ip'];
	
	if (isset($_POST['new_ban'], $_POST['reason'], $_POST['length'], $_POST['board'])) {
		require_once 'inc/mod/ban.php';
		
		if (isset($_POST['ip']))
			$ip = $_POST['ip'];
		
		ban($ip, $_POST['reason'], parse_time($_POST['length']), $_POST['board'] == '*' ? false : $_POST['board']);
		
		if (isset($_POST['public_message'], $_POST['message'])) {
			// public ban message
			$query = prepare(sprintf('UPDATE `posts_%s` SET `body` = CONCAT(`body`, :body) WHERE `id` = :id', $board));
			$query->bindValue(':id', $post);
			$query->bindValue(':body', sprintf($config['mod']['ban_message'], utf8tohtml($_POST['message'])));
			$query->execute() or error(db_error($query));
			
			modLog("Attached a public ban message to post #{$post}: " . utf8tohtml($_POST['message']));
			buildThread($thread ? $thread : $post);
			buildIndex();
		} elseif (isset($_POST['delete']) && (int) $_POST['delete']) {
			// Delete post
			deletePost($post);
			modLog("Deleted post #{$post}");
			// Rebuild board
			buildIndex();
		}
		
		header('Location: ?/' . sprintf($config['board_path'], $board) . $config['file_index'], true, $config['redirect_http']);
	}
	
	$args = array(
		'ip' => $ip,
		'hide_ip' => !hasPermission($config['mod']['show_ip'], $board),
		'post' => $post,
		'board' => $board,
		'delete' => (bool)$delete,
		'boards' => listBoards()
	);
	
	mod_page("New ban", 'mod/ban_form.html', $args);
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

function mod_deletefile($board, $post) {
	global $config, $mod;
	
	if (!openBoard($board))
		error($config['error']['noboard']);
	
	if (!hasPermission($config['mod']['deletefile'], $board))
		error($config['error']['noaccess']);
	
	// Delete file
	deleteFile($post);
	// Record the action
	modLog("Deleted file from post #{$post}");
	// Rebuild board
	buildIndex();
	
	// Redirect
	header('Location: ?/' . sprintf($config['board_path'], $board) . $config['file_index'], true, $config['redirect_http']);
}

function mod_deletebyip($boardName, $post, $global = false) {
	global $config, $mod, $board;
	
	$global = (bool)$global;
	
	if (!openBoard($boardName))
		error($config['error']['noboard']);
	
	if (!$global && !hasPermission($config['mod']['deletebyip'], $boardName))
		error($config['error']['noaccess']);
	
	if ($global && !hasPermission($config['mod']['deletebyip_global'], $boardName))
		error($config['error']['noaccess']);
	
	// Find IP address
	$query = prepare(sprintf('SELECT `ip` FROM `posts_%s` WHERE `id` = :id', $boardName));
	$query->bindValue(':id', $post);
	$query->execute() or error(db_error($query));
	if (!$ip = $query->fetchColumn(0))
		error($config['error']['invalidpost']);
	
	$boards = $global ? listBoards() : array(array('uri' => $boardName));
	
	$query = '';
	foreach ($boards as $_board) {
		$query .= sprintf("SELECT `id`, '%s' AS `board` FROM `posts_%s` WHERE `ip` = :ip UNION ALL ", $_board['uri'], $_board['uri']);
	}
	$query = preg_replace('/UNION ALL $/', '', $query);
	
	$query = prepare($query);
	$query->bindValue(':ip', $ip);
	$query->execute() or error(db_error($query));
	
	if ($query->rowCount() < 1)
		error($config['error']['invalidpost']);
	
	$boards = array();
	while ($post = $query->fetch()) {
		openBoard($post['board']);
		$boards[] = $post['board'];
		
		deletePost($post['id'], false);
	}
	
	$boards = array_unique($boards);
	
	foreach ($boards as $_board) {
		openBoard($_board);
		buildIndex();
	}
	
	if ($global) {
		$board = false;
	}
	
	// Record the action
	modLog("Deleted all posts by IP address: <a href=\"?/IP/$ip\">$ip</a>");
	
	// Redirect
	header('Location: ?/' . sprintf($config['board_path'], $boardName) . $config['file_index'], true, $config['redirect_http']);
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
		
		if (isset($_POST['delete'])) {
			if (!hasPermission($config['mod']['deleteusers']))
				error($config['error']['noaccess']);
			
			$query = prepare('DELETE FROM `mods` WHERE `id` = :id');
			$query->bindValue(':id', $uid);
			$query->execute() or error(db_error($query));
			
			modLog('Deleted user ' . utf8tohtml($user['username']) . ' <small>(#' . $user['id'] . ')</small>');
			
			header('Location: ?/users', true, $config['redirect_http']);
			
			return;
		}
		
		if ($_POST['username'] == '')
			error(sprintf($config['error']['required'], 'username'));
		
		$query = prepare('UPDATE `mods` SET `username` = :username, `boards` = :boards WHERE `id` = :id');
		$query->bindValue(':id', $uid);
		$query->bindValue(':username', $_POST['username']);
		$query->bindValue(':boards', implode(',', $boards));
		$query->execute() or error(db_error($query));
		
		if ($user['username'] !== $_POST['username']) {
			// account was renamed
			modLog('Renamed user "' . utf8tohtml($user['username']) . '" <small>(#' . $user['id'] . ')</small> to "' . utf8tohtml($_POST['username']) . '"');
		}
		
		if ($_POST['password'] != '') {
			$query = prepare('UPDATE `mods` SET `password` = SHA1(:password) WHERE `id` = :id');
			$query->bindValue(':id', $uid);
			$query->bindValue(':password', $_POST['password']);
			$query->execute() or error(db_error($query));
			
			modLog('Changed password for ' . utf8tohtml($_POST['username']) . ' <small>(#' . $user['id'] . ')</small>');
			
			if ($uid == $mod['id']) {
				login($_POST['username'], $_POST['password']);
				setCookies();
			}
		}
		
		if (hasPermission($config['mod']['manageusers']))
			header('Location: ?/users', true, $config['redirect_http']);
		else
			header('Location: ?/', true, $config['redirect_http']);
		
		return;
	}
	
	if (hasPermission($config['mod']['change_password']) && $uid == $mod['id'] && isset($_POST['password'])) {
		if ($_POST['password'] != '') {
			$query = prepare('UPDATE `mods` SET `password` = SHA1(:password) WHERE `id` = :id');
			$query->bindValue(':id', $uid);
			$query->bindValue(':password', $_POST['password']);
			$query->execute() or error(db_error($query));
			
			modLog('Changed own password');
			
			login($user['username'], $_POST['password']);
			setCookies();
		}
		
		if (hasPermission($config['mod']['manageusers']))
			header('Location: ?/users', true, $config['redirect_http']);
		else
			header('Location: ?/', true, $config['redirect_http']);
		
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

function mod_user_new() {
	global $pdo, $config;
	
	if (!hasPermission($config['mod']['createusers']))
		error($config['error']['noaccess']);
	
	if (isset($_POST['username'], $_POST['password'], $_POST['type'])) {
		if ($_POST['username'] == '')
			error(sprintf($config['error']['required'], 'username'));
		if ($_POST['password'] == '')
			error(sprintf($config['error']['required'], 'password'));
		
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
		
		$_POST['type'] = (int) $_POST['type'];
		if ($_POST['type'] !== JANITOR && $_POST['type'] !== MOD && $_POST['type'] !== ADMIN)
			error(sprintf($config['error']['invalidfield'], 'type'));
		
		$query = prepare('INSERT INTO `mods` VALUES (NULL, :username, SHA1(:password), :type, :boards)');
		$query->bindValue(':username', $_POST['username']);
		$query->bindValue(':password', $_POST['password']);
		$query->bindValue(':type', $_POST['type']);
		$query->bindValue(':boards', implode(',', $boards));
		$query->execute() or error(db_error($query));
		
		$uid = $pdo->lastInsertId();
		
		modLog('Created a new user: ' . utf8tohtml($_POST['username']) . ' <small>(#' . $userID . ')</small>');
		
		header('Location: ?/users', true, $config['redirect_http']);
		return;
	}
		
	mod_page('Edit user', 'mod/user.html', array('new' => true, 'boards' => listBoards()));
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
	
	if ($reply && !hasPermission($config['mod']['create_pm']))
		error($config['error']['noaccess']);
	
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
		
		mod_page("New PM for {$pm['to_username']}", 'mod/new_pm.html', array(
			'username' => $pm['username'], 'id' => $pm['sender'], 'message' => quote($pm['message'])
		));
	} else {
		mod_page("Private message &ndash; #$id", 'mod/pm.html', $pm);
	}
}

function mod_inbox() {
	global $config, $mod;
	
	$query = prepare('SELECT `unread`,`pms`.`id`, `time`, `sender`, `to`, `message`, `username` FROM `pms` LEFT JOIN `mods` ON `mods`.`id` = `sender` WHERE `to` = :mod ORDER BY `unread` DESC, `time` DESC');
	$query->bindValue(':mod', $mod['id']);
	$query->execute() or error(db_error($query));
	$messages = $query->fetchAll(PDO::FETCH_ASSOC);
	
	$query = prepare('SELECT COUNT(*) FROM `pms` WHERE `to` = :mod AND `unread` = 1');
	$query->bindValue(':mod', $mod['id']);
	$query->execute() or error(db_error($query));
	$unread = $query->fetchColumn(0);
	
	foreach ($messages as &$message) {
		$message['snippet'] = pm_snippet($message['message']);
	}
	
	mod_page('PM inbox (' . (count($messages) > 0 ? $unread . ' unread' : 'empty') . ')', 'mod/inbox.html', array(
		'messages' => $messages,
		'unread' => $unread
	));
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
			if ($config['cache']['enabled']) {
				$log[] = 'Flushing cache';
				Cache::flush();
			}
			
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
		
		if (isset($__old_body_truncate_char))
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
	global $pdo, $config;
	
	$args = array();
	
	if (isset($_POST['board'], $_POST['thread'])) {
		$where = '`board` = ' . $pdo->quote($_POST['board']);
		if ($_POST['thread'] != '')
			$where .= ' AND `thread` = ' . $pdo->quote($_POST['thread']);
		
		if (isset($_POST['purge'])) {
			$query = prepare('UPDATE `antispam` SET `expires` = UNIX_TIMESTAMP() + :expires WHERE' . $where);
			$query->bindValue(':expires', $config['spam']['hidden_inputs_expire']);
			$query->execute() or error(db_error());
		}
		
		$args['board'] = $_POST['board'];
		$args['thread'] = $_POST['thread'];
	} else {
		$where = '';
	}
	
	$query = query('SELECT COUNT(*) FROM `antispam`' . ($where ? " WHERE $where" : '')) or error(db_error());
	$args['total'] = number_format($query->fetchColumn(0));
	
	$query = query('SELECT COUNT(*) FROM `antispam` WHERE `expires` IS NOT NULL' . ($where ? " AND $where" : '')) or error(db_error());
	$args['expiring'] = number_format($query->fetchColumn(0));
	
	$query = query('SELECT * FROM `antispam` ' . ($where ? "WHERE $where" : '') . ' ORDER BY `passed` DESC LIMIT 40') or error(db_error());
	$args['top'] = $query->fetchAll(PDO::FETCH_ASSOC);
	
	mod_page("Debug: Anti-spam", 'mod/debug/antispam.html', $args);
}


