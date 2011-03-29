<?php
	require 'inc/functions.php';
	require 'inc/display.php';
	require 'inc/template.php';
	require 'inc/database.php';
	require 'inc/user.php';
	
	sql_open();
	
	// Check if banned
	checkBan();
			
	require 'inc/mod.php';
	
	// Fix some encoding issues
	header('Content-Type: text/html; charset=utf-8', true);
	
	if (get_magic_quotes_gpc()) {
		function strip_array($var) {
			return is_array($var) ? array_map("strip_array", $var) : stripslashes($var);
		}
		
		$_SESSION = strip_array($_SESSION);
		$_GET = strip_array($_GET);
		$_POST = strip_array($_POST);
	}
	
	$query = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
	
	// If not logged in
	if(!$mod) {
		if(isset($_POST['login'])) {
			// Check if inputs are set and not empty
			if(	!isset($_POST['username']) ||
				!isset($_POST['password']) ||
				empty($_POST['username']) ||
				empty($_POST['password'])
				) loginForm($config['error']['invalid'], $_POST['username'], '?' . $query);
			
			
			if(!login($_POST['username'], $_POST['password']))
				loginForm($config['error']['invalid'], $_POST['username'], '?' . $query);
			
			modLog("Logged in.");
			
			// Login successful
			// Set cookies
			setCookies();
			
			// Redirect
			if(isset($_POST['redirect']))
				header('Location: ' . $_POST['redirect'], true, $config['redirect_http']);
			else
				header('Location: ?' . $config['mod']['default'], true, $config['redirect_http']);
			
			// Close connection
			sql_close();
		} else {
			loginForm(false, false, '?' . $query);
		}
	} else {
		// Redirect (for index pages)
		if(count($_GET) == 2 && isset($_GET['status']) && isset($_GET['r']))
			header('Location: ' . $_GET['r'], true, $_GET['status']);
		
		// A sort of "cache"
		// Stops calling preg_quote and str_replace when not needed; only does it once
		$regex = Array(
			'board' => str_replace('%s', '(\w{1,8})', preg_quote($config['board_path'], '/')),
			'page' => str_replace('%d', '(\d+)', preg_quote($config['file_page'], '/')),
			'img' => preg_quote($config['dir']['img'], '/'),
			'thumb' => preg_quote($config['dir']['thumb'], '/'),
			'res' => preg_quote($config['dir']['res'], '/'),
			'index' => preg_quote($config['file_index'], '/')
		);
		
		if(preg_match('/^\/?$/', $query)) {
			// Dashboard
			$fieldset = Array(
				'Boards' => '',
				'Administration' => '',
				'Search' => '',
				'Logout' => ''
			);
			
			// Boards
			$fieldset['Boards'] .= ulBoards();
			
			if($mod['type'] >= $config['mod']['reports']) {
				$fieldset['Administration'] .= 	'<li><a href="?/reports">Report queue</a></li>';
			}
			if($mod['type'] >= $config['mod']['view_banlist']) {
				$fieldset['Administration'] .= 	'<li><a href="?/bans">Ban list</a></li>';
			}
			if($mod['type'] >= $config['mod']['manageusers']) {
				$fieldset['Administration'] .= 	'<li><a href="?/users">Manage users</a></li>';
			}
			if($mod['type'] >= $config['mod']['modlog']) {
				$fieldset['Administration'] .= 	'<li><a href="?/log">Moderation log</a></li>';
			}
			if($mod['type'] >= $config['mod']['rebuild']) {
				$fieldset['Administration'] .= 	'<li><a href="?/rebuild">Rebuild static files</a></li>';
			}
			if($mod['type'] >= $config['mod']['show_config']) {
				$fieldset['Administration'] .= 	'<li><a href="?/config">Show configuration</a></li>';
			}
			
			if($mod['type'] >= $config['mod']['search']) {
				$fieldset['Search'] .= 	'<li><form style="display:inline" action="?/search" method="post">' .
				'<label style="display:inline" for="search">Phrase:</label> ' .
					'<input id="search" name="search" type="text" size="35" />' .
					'<input type="submit" value="Search" />' .
				'</form>' .
					'<p class="unimportant">(Search is case-insensitive but not based on keywords.)</p>' .
				'</li>';
			}
			
			$fieldset['Logout'] .= '<li><a href="?/logout">Logout</a></li>';
			
			// TODO: Statistics, etc, in the dashboard.
			
			$body = '';
			foreach($fieldset as $title => $data) {
				if($data)
					$body .= "<fieldset><legend>{$title}</legend><ul>{$data}</ul></fieldset>";
			}
			
			echo Element('page.html', Array(
				'config'=>$config,
				'title'=>'Dashboard',
				'body'=>$body,
				'__mod'=>true
				)
			);
		} elseif(preg_match('/^\/logout$/', $query)) {
			destroyCookies();
			
			header('Location: ?/', true, $config['redirect_http']);
		} elseif(preg_match('/^\/log$/', $query)) {
			if($mod['type'] < $config['mod']['modlog']) error($config['error']['noaccess']);
			
			$boards = Array();
			$_boards = listBoards();
			foreach($_boards as &$_b) {
				$boards[$_b['id']] = $_b['uri'];
			}
			
			$body = '<table class="modlog"><tr><th>User</th><th>IP address</th><th>Ago</th><th>Board</th><th>Action</th></tr>';
			
			$query = prepare("SELECT `mods`.`id`,`username`,`ip`,`board`,`time`,`text` FROM `modlogs` INNER JOIN `mods` ON `mod` = `mods`.`id` ORDER BY `time` DESC LIMIT :limit");
			$query->bindValue(':limit', $config['mod']['modlog_page'], PDO::PARAM_INT);
			$query->execute() or error(db_error($query));
			
			while($log = $query->fetch()) {
				$log['text'] = htmlentities($log['text']);
				$log['text'] = preg_replace('/(\d+\.\d+\.\d+\.\d+)/', '<a href="?/IP/$1">$1</a>', $log['text']);
				
				
				$body .= '<tr>' .
				'<td class="minimal"><a href="?/users/' . $log['id'] . '">' . $log['username'] . '</a></td>' .
				'<td class="minimal"><a href="?/IP/' . $log['ip'] . '">' . $log['ip'] . '</a></td>' .
				'<td class="minimal">' . ago($log['time']) . '</td>' .
				'<td class="minimal">' .
					($log['board'] ?
						(isset($boards[$log['board']]) ?
							'<a href="?/' . $boards[$log['board']] . '/' . $config['file_index'] . '">' . sprintf($config['board_abbreviation'], $boards[$log['board']]) . '</a></td>'
						: '<em>deleted?</em>')
					: '-') .
				'<td>' . $log['text'] . '</td>' .
				'</tr>';
			}
			
			$body .= '</table>';
			
			echo Element('page.html', Array(
				'config'=>$config,
				'title'=>'Moderation log',
				'body'=>$body,
				'mod'=>true
				)
			);
		} elseif(preg_match('/^\/PM\/(\d+)$/', $query, $match)) {
			$id = $match[1];
			
			$query = prepare("SELECT `pms`.`id`, `time`, `sender`, `message`, `username` FROM `pms` LEFT JOIN `mods` ON `mods`.`id` = `sender` WHERE `pms`.`id` = :id AND `to` = :mod");
			$query->bindValue(':id', $id, PDO::PARAM_INT);
			$query->bindValue(':mod', $mod['id'], PDO::PARAM_INT);
			$query->execute() or error(db_error($query));
			
			if(!$pm = $query->fetch()) {
				// Mod doesn't exist
				error($config['error']['404']);
			}
			
			if(isset($_POST['delete'])) {
				$query = prepare("DELETE FROM `pms` WHERE `id` = :id");
				$query->bindValue(':id', $id, PDO::PARAM_INT);
				$query->execute() or error(db_error($query));
				
				modLog('Deleted a PM');
				
				header('Location: ?/', true, $config['redirect_http']);
			} else {
				$query = prepare("UPDATE `pms` SET `unread` = 0 WHERE `id` = :id");
				$query->bindValue(':id', $id, PDO::PARAM_INT);
				$query->execute() or error(db_error($query));
				
				modLog('Read a PM');
				
				$body = '<form action="" method="post"><table><th>From</th><td>' .
					($mod['type'] >= $config['mod']['editusers'] ?
						'<a href="?/users/' . $pm['sender'] . '">' . htmlentities($pm['username']) . '</a>' :
						htmlentities($pm['username'])
					) .
				'</td></tr>' .
				
				'<tr><th>Date</th><td> ' . date($config['post_date'], $pm['time']) . '</td></tr>' .
				
				'<tr><th>Message</th><td> ' . $pm['message'] . '</td></tr>' .
				
				'</table>' . 
				
				'<p style="text-align:center"><input type="submit" name="delete" value="Delete forever" /></p>' .
				
				'</form>';
				
				echo Element('page.html', Array(
					'config'=>$config,
					'title'=>'Private message',
					'body'=>$body,
					'mod'=>true
					)
				);
			}
		} elseif(preg_match('/^\/new_PM\/(\d+)$/', $query, $match)) {
			if($mod['type'] < $config['mod']['create_pm']) error($config['error']['noaccess']);
			
			$to = $match[1];
			
			$query = prepare("SELECT `username`,`id` FROM `mods` WHERE `id` = :id");
			$query->bindValue(':id', $to, PDO::PARAM_INT);
			$query->execute() or error(db_error($query));
			
			if(!$to = $query->fetch()) {
				// Mod doesn't exist
				error($config['error']['404']);
			}
			
			if(isset($_POST['message'])) {
				// Post message
				$message = $_POST['message'];
				
				if(empty($message))
					error($config['error']['tooshort_body']);
				
				markup($message);
				
				$query = prepare("INSERT INTO `pms` VALUES (NULL, :sender, :to, :message, :time, 1)");
				$query->bindValue(':sender', $mod['id'], PDO::PARAM_INT);
				$query->bindValue(':to', $to['id'], PDO::PARAM_INT);
				$query->bindValue(':message', $message);
				$query->bindValue(':time', time(), PDO::PARAM_INT);
				$query->execute() or error(db_error($query));
				
				modLog('Sent a PM to ' . $to['username']);
				
				echo Element('page.html', Array(
					'config'=>$config,
					'title'=>'PM sent',
					'body'=>'<p style="text-align:center">Message sent successfully to ' . htmlentities($to['username']) . '.</p>',
					'mod'=>true
					)
				);
			} else {
				$body = '<form action="" method="post">' .
				
				'<table>' . 
				
				'<tr><th>To</th><td>' .
					($mod['type'] >= $config['mod']['editusers'] ?
						'<a href="?/users/' . $to['id'] . '">' . htmlentities($to['username']) . '</a>' :
						htmlentities($to['username'])
					) .
				'</td>' .
				
				'<tr><th>Message</th><td><textarea name="message" rows="10" cols="40"></textarea></td>' .
				
				'</table>' .
				
				'<p style="text-align:center"><input type="submit" value="Send message" /></p>' .
				
				'</form>';
				
				echo Element('page.html', Array(
					'config'=>$config,
					'title'=>'New PM for ' . htmlentities($to['username']),
					'body'=>$body,
					'mod'=>true
					)
				);
			}
		} elseif(preg_match('/^\/search$/', $query)) {
			if($mod['type'] < $config['mod']['search']) error($config['error']['noaccess']);
			
			$body = '<div class="ban"><h2>Search</h2><form style="display:inline" action="?/search" method="post">' .
				'<p><label style="display:inline" for="search">Phrase:</label> ' .
					'<input id="search" name="search" type="text" size="35" ' .
						(isset($_POST['search']) ? 'value="' . htmlentities($_POST['search']) . '" ' : '') .
					'/>' .
					'<input type="submit" value="Search" />' .
				'</p></form>' .
					'<p><span class="unimportant">(Search is case-insensitive but not based on keywords.)</span></p>' .
				'</div>';
			
			if(isset($_POST['search']) && !empty($_POST['search'])) {
				$phrase = $_POST['search'];
				$_body = '';
				
				$boards = listBoards();
				foreach($boards as &$_b) {
					openBoard($_b['uri']);
					
					$query = prepare(sprintf("SELECT * FROM `posts_%s` WHERE `body` LIKE :query ORDER BY `time` DESC LIMIT :limit", $board['uri']));
					$query->bindValue(':query', "%{$phrase}%");
					$query->bindValue(':limit', $config['mod']['search_results'], PDO::PARAM_INT);
					$query->execute() or error(db_error($query));
					
					$temp = '';
					while($post = $query->fetch()) {
						if(!$post['thread']) {
							$po = new Thread($post['id'], $post['subject'], $post['email'], $post['name'], $post['trip'], $post['body'], $post['time'], $post['thumb'], $post['thumbwidth'], $post['thumbheight'], $post['file'], $post['filewidth'], $post['fileheight'], $post['filesize'], $post['filename'], $post['ip'], $post['sticky'], $post['locked'], '?/', $mod, false);
						} else {
							$po = new Post($post['id'], $post['thread'], $post['subject'], $post['email'], $post['name'], $post['trip'], $post['body'], $post['time'], $post['thumb'], $post['thumbwidth'], $post['thumbheight'], $post['file'], $post['filewidth'], $post['fileheight'], $post['filesize'], $post['filename'], $post['ip'], '?/', $mod);
						}
						$temp .= $po->build(true) . '<hr/>';
					}
					
					if(!empty($temp))
						$_body .= '<fieldset><legend>' . $query->rowCount() . ' result' . ($query->rowCount() != 1 ? 's' : '') . ' on <a href="?/' .
								sprintf($config['board_path'], $board['uri']) . $config['file_index'] .
						'">' .
						sprintf($config['board_abbreviation'], $board['uri']) . ' - ' . $board['title'] .
						'</a></legend>' . $temp . '</fieldset>';
				}
				
				$body .= '<hr/>';
				if(!empty($_body))
					$body .= $_body;
				else
					$body .= '<p style="text-align:center" class="unimportant">(No results.)</p>';
			}
				
			echo Element('page.html', Array(
				'config'=>$config,
				'title'=>'Search',
				'body'=>$body,
				'mod'=>true
				)
			);
		} elseif(preg_match('/^\/users$/', $query)) {
			if($mod['type'] < $config['mod']['manageusers']) error($config['error']['noaccess']);
			
			$body = '<form action="" method="post"><table><tr><th>ID</th><th>Username</th><th>Type</th><th>Last action</th><th>…</th></tr>';
			
			$query = query("SELECT *, (SELECT `time` FROM `modlogs` WHERE `mod` = `id` ORDER BY `time` DESC LIMIT 1) AS `last`, (SELECT `text` FROM `modlogs` WHERE `mod` = `id` ORDER BY `time` DESC LIMIT 1) AS `action` FROM `mods` ORDER BY `type` DESC,`id`") or error(db_error());
			while($_mod = $query->fetch()) {				
				$type = $_mod['type'] == JANITOR ? 'Janitor' : ($_mod['type'] == MOD ? 'Mod' : 'Admin');
				$body .= '<tr>' .
					'<td>' .
						$_mod['id'] .
					'</td>' .
					
					'<td>' .
						$_mod['username'] .
					'</td>' .
					
					'<td>' .
						$type .
					'</td>' .
					
					'<td>' .
						($_mod['last'] ?
							'<span title="' . htmlentities($_mod['action']) . '">' . ago($_mod['last']) . '</span>'
						: '<em>never</em>') .
					'</td>' .
					
					'<td style="white-space:nowrap">' .
						($mod['type'] >= $config['mod']['promoteusers'] ?
							($_mod['type'] != ADMIN ?
								'<a style="text-decoration:none" href="?/users/' . $_mod['id'] . '/promote" title="Promote">▲</a>'
							:'') .
							($_mod['type'] != JANITOR ?
								'<a style="text-decoration:none" href="?/users/' . $_mod['id'] . '/demote" title="Demote">▼</a>'
							:'')
						: ''
						) .
						($mod['type'] >= $config['mod']['editusers'] ||
						($mod['type'] >= $config['mod']['change_password'] && $_mod['id'] == $mod['id'])?
							'<a class="unimportant" style="margin-left:5px;float:right" href="?/users/' . $_mod['id'] . '">[edit]</a>'
						: '' ) .
						($mod['type'] >= $config['mod']['create_pm'] ?
							'<a class="unimportant" style="margin-left:5px;float:right" href="?/new_PM/' . $_mod['id'] . '">[PM]</a>'
						: '' ) .
					'</td></tr>';
			}
			
			$body .= '</table>';
			
			if($mod['type'] >= $config['mod']['createusers']) {
				$body .= '<p style="text-align:center"><a href="?/users/new">Create new user</a></p>';
			}
			
			$body .= '</form>';
			
			echo Element('page.html', Array(
				'config'=>$config,
				'title'=>'Manage users',
				'body'=>$body
				,'mod'=>true
				)
			);
		} elseif(preg_match('/^\/users\/new$/', $query)) {
			if($mod['type'] < $config['mod']['createusers']) error($config['error']['noaccess']);
			
			if(isset($_POST['username']) && isset($_POST['password'])) {
				if(!isset($_POST['type'])) {
					error(sprintf($config['error']['required'], 'type'));
				}
				
				if($_POST['type'] != ADMIN && $_POST['type'] != MOD && $_POST['type'] != JANITOR) {
					error(sprintf($config['error']['invalidfield'], 'type'));
				}
				
				// Check if already exists
				$query = prepare("SELECT `id` FROM `mods` WHERE `username` = :username");
				$query->bindValue(':username', $_POST['username']);
				$query->execute() or error(db_error($query));
				
				if($_mod = $query->fetch()) {
					error(sprintf($config['error']['modexists'], $_mod['id']));
				}
				
				$query = prepare("INSERT INTO `mods` VALUES (NULL, :username, :password, :type)");
				$query->bindValue(':username', $_POST['username']);
				$query->bindValue(':password', sha1($_POST['password']));
				$query->bindValue(':type', $_POST['type'], PDO::PARAM_INT);
				$query->execute() or error(db_error($query));
				
				modLog('Create a new user: "' . $_POST['username'] . '"');
			}
			
			$body = '<fieldset><legend>New user</legend>' . 
				
				// Begin form
				'<form style="text-align:center" action="" method="post">' .
				
				'<table>' .
				
				'<tr><th>Username</th><td><input size="20" maxlength="30" type="text" name="username" value="" autocomplete="off" /></td></tr>' .
				'<tr><th>Password</th><td><input size="20" maxlength="30" type="password" name="password" value="" autocomplete="off" /></td></tr>' .
				'<tr><th>Type</th><td>' .
					'<div><label for="janitor">Janitor</label> <input type="radio" id="janitor" name="type" value="' . JANITOR . '" /></div>' .
					'<div><label for="mod">Mod</label> <input type="radio" id="mod" name="type" value="' . MOD . '" /></div>' .
					'<div><label for="admin">Admin</label> <input type="radio" id="admin" name="type" value="' . ADMIN . '" /></div>' .
				'</td></tr>' .
				'</table>' .
				
				'<input style="margin-top:10px" type="submit" value="Create user" />' .
				
				// End form
				'</form></fieldset>';
				
				echo Element('page.html', Array(
					'config'=>$config,
					'title'=>'New user',
					'body'=>$body
					,'mod'=>true
					)
				);
		} elseif(preg_match('/^\/users\/(\d+)(\/(promote|demote|delete))?$/', $query, $matches)) {
			$modID = $matches[1];
			
			if(isset($matches[2])) {
				if($matches[3] == 'delete') {
					if($mod['type'] < $config['mod']['deleteusers']) error($config['error']['noaccess']);
					
					$query = prepare("DELETE FROM `mods` WHERE `id` = :id");
					$query->bindValue(':id', $modID, PDO::PARAM_INT);
					$query->execute() or error(db_error($query));
					
					modLog('Deleted user #' . $modID);
				} else {
					// Promote/demote
					if($mod['type'] < $config['mod']['promoteusers']) error($config['error']['noaccess']);
					
					if($matches[3] == 'promote') {
						$query = prepare("UPDATE `mods` SET `type` = `type` + 1 WHERE `type` != :admin AND `id` = :id");
						$query->bindValue(':admin', ADMIN, PDO::PARAM_INT);
					} else {
						$query = prepare("UPDATE `mods` SET `type` = `type` - 1 WHERE `type` != :janitor AND `id` = :id");
						$query->bindValue(':janitor', JANITOR, PDO::PARAM_INT);
					}
					
					$query->bindValue(':id', $modID, PDO::PARAM_INT);
					$query->execute() or error(db_error($query));
				}
				header('Location: ?/users', true, $config['redirect_http']);
			} else {
				// Edit user
				if($mod['type'] < $config['mod']['editusers'] && $mod['type'] < $config['mod']['change_password']) error($config['error']['noaccess']);
				
				$query = prepare("SELECT * FROM `mods` WHERE `id` = :id");
				$query->bindValue(':id', $modID, PDO::PARAM_INT);
				$query->execute() or error(db_error($query));
				
				if(!$_mod = $query->fetch()) {
					error($config['error']['404']);
				}
				
				if($mod['type'] < $config['mod']['editusers'] && !($mod['type'] >= $config['mod']['change_password'] && $mod['id'] == $_mod['id'] && $change_password_only = true))
					error($config['error']['noaccess']);
				
				if((isset($_POST['username']) && isset($_POST['password'])) || (isset($change_password_only) && isset($_POST['password']))) {
					if(!isset($change_password_only)) {
						$query = prepare("UPDATE `mods` SET `username` = :username WHERE `id` = :id");
						$query->bindValue(':username', $_POST['username']);
						$query->bindValue(':id', $modID, PDO::PARAM_INT);
						$query->execute() or error(db_error($query));
						modLog('Edited login details for user "' . $_mod['username'] . '"');
					} else {
						modLog('Changed own password');
					}
					if(!empty($_POST['password'])) {
						$query = prepare("UPDATE `mods` SET `password` = :password WHERE `id` = :id");
						$query->bindValue(':password', sha1($_POST['password']));
						$query->bindValue(':id', $modID, PDO::PARAM_INT);
						$query->execute() or error(db_error($query));
					}
					
					// Refresh
					$query = prepare("SELECT * FROM `mods` WHERE `id` = :id");
					$query->bindValue(':id', $modID, PDO::PARAM_INT);
					$query->execute() or error(db_error($query));
					
					if(!$_mod = $query->fetch()) {
						error($config['error']['404']);
					}
				
					if($_mod['id'] == $mod['id']) {
						// Changed own password. Update cookies
						var_dump(login($_mod['username'], $_mod['password'], false));
						setCookies();
					}
				}
				
				$body = '<fieldset><legend>Edit user</legend>' . 
				
				// Begin form
				'<form style="text-align:center" action="" method="post">' .
				
				'<table>' .
				
				'<tr><th>Username</th><td>' . 
				
				(isset($change_password_only) ?
					$_mod['username']
				: '<input size="20" maxlength="30" type="text" name="username" value="' . $_mod['username'] . '" autocomplete="off" />') .
				
				'</td></tr>' .
				'<tr><th>Password <span class="unimportant">(new; optional)</span></th><td><input size="20" maxlength="30" type="password" name="password" value="" autocomplete="off" /></td></tr>' .
				'</table>' .
				
				'<input type="submit" value="Save changes" />' .
				
				// End form
				'</form> ' .
				
				// Delete button
				($mod['type'] >= $config['mod']['deleteusers'] ?
					'<p style="text-align:center"><a href="?/users/' . $_mod['id'] . '/delete">Delete user</a></p>'
				:'') .
				
				'</fieldset>';
				
				echo Element('page.html', Array(
					'config'=>$config,
					'title'=>'Edit user',
					'body'=>$body
					,'mod'=>true
					)
				);
			}
		} elseif(preg_match('/^\/reports$/', $query)) {
			if($mod['type'] < $config['mod']['reports']) error($config['error']['noaccess']);
			
			$body = '';
			$reports = 0;
			
			$query = prepare("SELECT `reports`.*, `boards`.`uri` FROM `reports` INNER JOIN `boards` ON `board` = `boards`.`id` ORDER BY `time` DESC LIMIT :limit");
			$query->bindValue(':limit', $config['mod']['recent_reports'], PDO::PARAM_INT);
			$query->execute() or error(db_error($query));
			
			while($report = $query->fetch()) {
				$p_query = prepare(sprintf("SELECT * FROM `posts_%s` WHERE `id` = :id", $report['uri']));
				$p_query->bindValue(':id', $report['post'], PDO::PARAM_INT);
				$p_query->execute() or error(db_error($query));
				
				if(!$post = $p_query->fetch()) {
					// Invalid report (post has since been deleted)
					$p_query = prepare("DELETE FROM `reports` WHERE `post` = :id");
					$p_query->bindValue(':id', $report['post'], PDO::PARAM_INT);
					$p_query->execute() or error(db_error($query));
					continue;
				}
				
				$reports++;
				openBoard($report['uri']);
				
				if(!$post['thread']) {
					$po = new Thread($post['id'], $post['subject'], $post['email'], $post['name'], $post['trip'], $post['body'], $post['time'], $post['thumb'], $post['thumbwidth'], $post['thumbheight'], $post['file'], $post['filewidth'], $post['fileheight'], $post['filesize'], $post['filename'], $post['ip'], $post['sticky'], $post['locked'], '?/', $mod, false);
				} else {
					$po = new Post($post['id'], $post['thread'], $post['subject'], $post['email'], $post['name'], $post['trip'], $post['body'], $post['time'], $post['thumb'], $post['thumbwidth'], $post['thumbheight'], $post['file'], $post['filewidth'], $post['fileheight'], $post['filesize'], $post['filename'], $post['ip'], '?/', $mod);
				}
				
				$po->body .=
					'<div class="report">' .
						'<hr/>' .
						'Board: <a href="?/' . $report['uri'] . '/' . $config['file_index'] . '">' . sprintf($config['board_abbreviation'], $report['uri']) . '</a><br/>' .
						'Reason: ' . $report['reason'] . '<br/>' .
						'Reported by: <a href="?/IP/' . $report['ip'] . '">' . $report['ip'] . '</a><br/>' .
						'<hr/>' .
							($mod['type'] >= $config['mod']['report_dismiss'] ?
								'<a title="Discard abuse report" href="?/reports/' . $report['id'] . '/dismiss">Dismiss</a> | ' : '') .
							($mod['type'] >= $config['mod']['report_dismiss_ip'] ?
								'<a title="Discard all abuse reports by this user" href="?/reports/' . $report['id'] . '/dismiss/all">Dismiss+</a>' : '') .
					'</div>';
				$body .= $po->build(true) . '<hr/>';
			}
			
			$query = query("SELECT COUNT(`id`) AS `count` FROM `reports`") or error(db_error());
			$count = $query->fetch();
			
			$body .= '<p class="unimportant" style="text-align:center">Showing ' . 
				($reports == $count['count'] ? 'all ' . $reports . ' reports' : $reports . ' of ' . $count['count'] . ' reports') . '.</p>';
			
			echo Element('page.html', Array(
				'config'=>$config,
				'title'=>'Report queue (' . $count['count'] . ')',
				'body'=>$body,
				'mod'=>true
			));
		} elseif(preg_match('/^\/reports\/(\d+)\/dismiss(\/all)?$/', $query, $matches)) {
			if(isset($matches[2]) && $matches[2] == '/all') {
				if($mod['type'] < $config['mod']['report_dismiss_ip']) error($config['error']['noaccess']);
				
				$query = prepare("SELECT `ip` FROM `reports` WHERE `id` = :id");
				$query->bindValue(':id', $matches[1], PDO::PARAM_INT);
				$query->execute() or error(db_error($query));
				
				if($report = $query->fetch()) {
					$query = prepare("DELETE FROM `reports` WHERE `ip` = :ip");
					$query->bindValue(':ip', $report['ip'], PDO::PARAM_INT);
					$query->execute() or error(db_error($query));
					
					modLog('Dismissed all reports by ' . $report['ip']);
				}
			} else {
				if($mod['type'] < $config['mod']['report_dismiss']) error($config['error']['noaccess']);
				
				$query = prepare("SELECT `post` FROM `reports` WHERE `id` = :id");
				$query->bindValue(':id', $matches[1], PDO::PARAM_INT);
				$query->execute() or error(db_error($query));
				
				if($report = $query->fetch()) {
					modLog('Dismissed a report for post #' . $report['post']);
					
					$query = prepare("DELETE FROM `reports` WHERE `post` = :post");
					$query->bindValue(':post', $report['post'], PDO::PARAM_INT);
					$query->execute() or error(db_error($query));
				}
			}
			
			// Redirect
			if(isset($_SERVER['HTTP_REFERER']))
				header('Location: ' . $_SERVER['HTTP_REFERER'], true, $config['redirect_http']);
			else
				header('Location: ?/reports', true, $config['redirect_http']);
		} elseif(preg_match('/^\/board\/(\w+)(\/delete)?$/', $query, $matches)) {
			if($mod['type'] < $config['mod']['manageboards']) error($config['error']['noaccess']);
			
			if(!openBoard($matches[1]))
				error($config['error']['noboard']);
			
			if(isset($matches[2]) && $matches[2] == '/delete') {
				if($mod['type'] < $config['mod']['deleteboard']) error($config['error']['noaccess']);
				// Delete board
				
				modLog('Deleted board ' . sprintf($config['board_abbreviation'], $board['uri']));
				
				// Delete entire board directory
				rrmdir($board['uri'] . '/');
				
				// Delete posting table
				$query = query(sprintf("DROP TABLE IF EXISTS `posts_%s`", $board['uri'])) or error(db_error());
				
				// Clear reports
				$query = prepare("DELETE FROM `reports` WHERE `board` = :id");
				$query->bindValue(':id', $board['id'], PDO::PARAM_INT);
				$query->execute() or error(db_error($query));
				
				// Delete from table
				$query = prepare("DELETE FROM `boards` WHERE `id` = :id");
				$query->bindValue(':id', $board['id'], PDO::PARAM_INT);
				$query->execute() or error(db_error($query));
				
				header('Location: ?/', true, $config['redirect_http']);
			} else {
				if(isset($_POST['title']) && isset($_POST['subtitle'])) {
					$query = prepare("UPDATE `boards` SET `title` = :title, `subtitle` = :subtitle WHERE `id` = :id");
					$query->bindValue(':title', utf8tohtml($_POST['title'], true));
					
					if(!empty($_POST['subtitle']))
						$query->bindValue(':subtitle', utf8tohtml($_POST['subtitle'], true));
					else
						$query->bindValue(':subtitle', null, PDO::PARAM_NULL);
					
					$query->bindValue(':id', $board['id'], PDO::PARAM_INT);
					$query->execute() or error(db_error($query));
					
					openBoard($board['uri']);
				}
				
				$body =
				'<fieldset><legend><a href="?/' .
				$board['uri'] .	'/' . $config['file_index'] . '">' .
				sprintf($config['board_abbreviation'], $board['uri']) . '</a>' . 
				' - ' . $board['name'] . '</legend>' . 
				
				// Begin form
				'<form style="text-align:center" action="" method="post">' .
				
				'<table>' .
				
				'<tr><th>URI</th><td>' . $board['uri'] . '</td>' .
				'<tr><th>Title</th><td><input size="20" maxlength="20" type="text" name="title" value="' . $board['name'] . '" /></td></tr>' .
				'<tr><th>Subtitle</th><td><input size="20" maxlength="40" type="text" name="subtitle" value="' .
					(isset($board['title']) ? $board['title'] : '') . '" /></td></tr>' .
				
				'</table>' .
				
				'<input type="submit" value="Update" />' .
				
				// End form
				'</form> ' .
				
				// Delete button
				($mod['type'] >= $config['mod']['deleteboard'] ?
					'<p style="text-align:center"><a href="?/board/' . $board['uri'] . '/delete">Delete board</a></p>'
				:'') .
				
				'</fieldset>';
				
				echo Element('page.html', Array(
					'config'=>$config,
					'title'=>'Manage – ' . sprintf($config['board_abbreviation'], $board['uri']),
					'body'=>$body,
					'mod'=>true
				));
			}
		} elseif(preg_match('/^\/bans$/', $query)) {
			if($mod['type'] < $config['mod']['view_banlist']) error($config['error']['noaccess']);
			
			if(isset($_POST['unban'])) {
				if($mod['type'] < $config['mod']['unban']) error($config['error']['noaccess']);
				
				foreach($_POST as $post => $value) {
					if(preg_match('/^ban_(.+)$/', $post, $m)) {
						$m[1] = str_replace('_', '.', $m[1]);
						$query = prepare("DELETE FROM `bans` WHERE `ip` = :ip");
						$query->bindValue(':ip', $m[1]);
						$query->execute() or error(db_error($query));
					}
				}
			}
			
			if($mod['type'] >= $config['mod']['view_banexpired']) {
				$query = prepare("SELECT * FROM `bans` INNER JOIN `mods` ON `mod` = `id` GROUP BY `ip` ORDER BY (`expires` IS NOT NULL AND `expires` < :time), `set` DESC");
				$query->bindValue(':time', time(), PDO::PARAM_INT);
				$query->execute() or error(db_error($query));
			} else {
				// Filter out expired bans
				$query = prepare("SELECT * FROM `bans` INNER JOIN `mods` ON `mod` = `id` GROUP BY `ip` WHERE `expires` = 0 OR `expires` > :time ORDER BY `set` DESC");
				$query->bindValue(':time', time(), PDO::PARAM_INT);
				$query->execute() or error(db_error($query));
			}
			
			if($query->rowCount() < 1) {
				$body = '(There are no active bans.)';
			} else {
				$body = '<form action="" method="post">';
				$body .= '<table><tr><th>IP address</th><th>Reason</th><th>Set</th><th>Expires</th><th>Staff</th></tr>';
				
				while($ban = $query->fetch()) {
					$body .=
						'<tr' .
							($config['mod']['view_banexpired'] && $ban['expires'] != 0 && $ban['expires'] < time() ?
								' style="text-decoration:line-through"'
							:'') .
						'>' .
					
					'<td style="white-space: nowrap">' .
					
					// Checkbox
					'<input type="checkbox" name="ban_' . $ban['ip'] . '" id="ban_' . $ban['ip'] . '" /> ' .
					
					// IP address
					'<a href="?/IP/' .
						$ban['ip'] .
					'">'. $ban['ip'] . '</a></td>' .
					
					// Reason
					'<td>' . ($ban['reason'] ? $ban['reason'] : '<em>-</em>') . '</td>' .
					
					// Set
					'<td style="white-space: nowrap">' . date($config['post_date'], $ban['set']) . '</td>' .
					
					// Expires
					'<td style="white-space: nowrap">' . 
						($ban['expires'] == 0 ?
							'<em>Never</em>'
						:
							date($config['post_date'], $ban['expires'])
						) .
					'</td>' .
					
					// Staff
					'<td>' .
						($mod['type'] < $config['mod']['view_banstaff'] ?
							($config['mod']['view_banquestionmark'] ?
								'?'
							:
								($ban['type'] == JANITOR ? 'Janitor' :
								($ban['type'] == MOD ? 'Mod' :
								($ban['type'] == ADMIN ? 'Admin' :
								'?')))
							)
						:
							$ban['username']
						) .
					'</td>' .
					
					'</tr>';
				}
				
				$body .= '</table>' .
				
				($mod['type'] >= $config['mod']['unban'] ?
					'<p style="text-align:center"><input name="unban" type="submit" value="Unban selected" /></p>'
				: '') .
				
				'</form>';
			}
			
			echo Element('page.html', Array(
				'config'=>$config,
				'title'=>'Ban list',
				'body'=>$body,
				'mod'=>true
			)
		);
		} elseif(preg_match('/^\/rebuild$/', $query)) {
			if($mod['type'] < $config['mod']['rebuild']) error($config['error']['noaccess']);
			
			set_time_limit($config['mod']['rebuild_timelimit']);
			
			$body = '<div class="ban"><h2>Rebuilding…</h2><p>';
			
			$body .= 'Generating Javascript file…<br/>';
			buildJavascript();
			
			$boards = listBoards();
			
			foreach($boards as &$board) {
				$body .= "<strong style=\"display:inline-block;margin: 15px 0 2px 0;\">Opening board /{$board['uri']}/</strong><br/>";
				openBoard($board['uri']);
				
				$body .= 'Creating index pages<br/>';
				buildIndex();
				
				$query = query(sprintf("SELECT `id` FROM `posts_%s` WHERE `thread` IS NULL", $board['uri'])) or error(db_error());
				while($post = $query->fetch()) {
					$body .= "Rebuilding #{$post['id']}<br/>";
					buildThread($post['id']);
				}
			}
			$body .= 'Complete!</p></div>';
			
			unset($board);
			modLog('Rebuilt everything');
			
			echo Element('page.html', Array(
				'config'=>$config,
				'title'=>'Rebuilt',
				'body'=>$body,
				'mod'=>true
			));
		} elseif(preg_match('/^\/config$/', $query)) {
			if($mod['type'] < $config['mod']['show_config']) error($config['error']['noaccess']);
			
			// Show instance-config.php	
			
			$data = '';
			
			function do_array_part($array, $prefix = '') {
				global $data, $config;
				
				foreach($array as $name => $value) {
					if(is_array($value)) {
						do_array_part($value, $prefix . $name . ' → ');
					} else {
						if($config['mod']['never_reveal_password'] && $prefix == 'db → ' && $name == 'password') {
							$value = '<em>hidden</em>';
						} elseif(gettype($value) == 'boolean') {
							$value = $value ? '<span style="color:green;">On</span>' : '<span style="color:red;">Off</span>';
						} elseif(gettype($value) == 'string') {
							if(empty($value))
								$value = '<em>empty</em>';
							else
								$value = '<span style="color:maroon;">' . utf8tohtml(substr($value, 0, 110) . (strlen($value) > 110 ? '…' : '')) . '</span>';
						} elseif(gettype($value) == 'integer') {
							$value = '<span style="color:black;">' . $value . '</span>';
						}
						
						$data .= 
								'<tr><th style="text-align:left;">' . 
									$prefix . (gettype($name) == 'integer' ? '[]' : $name) .
								'</th><td>' .
									$value .
								'</td></tr>';
						}
					}
				}
			
			do_array_part($config);				
			
			$body = '<fieldset><legend>Configuration</legend><table>' . $data . '</table></fieldset>';
			
			echo Element('page.html', Array(
				'config'=>$config,
				'title'=>'Configuration',
				'body'=>$body,
				'mod'=>true
				)
			);
		} elseif(preg_match('/^\/new$/', $query)) {
			if($mod['type'] < $config['mod']['newboard']) error($config['error']['noaccess']);
			
			// New board
			$body = '';
			
			if(isset($_POST['new_board'])) {
				// Create new board
				if(	!isset($_POST['uri']) ||
					!isset($_POST['title']) ||
					!isset($_POST['subtitle'])
				)	error($config['error']['missedafield']);
				
				$b = Array(
					'uri' => $_POST['uri'],
					'title' => $_POST['title'],
					'subtitle' => $_POST['subtitle']
				);
				
				// HTML characters
				$b['title'] = utf8tohtml($b['title'], true);
				$b['subtitle'] = utf8tohtml($b['subtitle'], true);
				
				// Check required fields
				if(empty($b['uri']))
					error(sprintf($config['error']['required'], 'URI'));
				if(empty($b['title']))
					error(sprintf($config['error']['required'], 'title'));
				
				// Check string lengths
				if(strlen($b['uri']) > 8)
					error(sprintf($config['error']['toolong'], 'URI'));
				if(strlen($b['title']) > 20)
					error(sprintf($config['error']['toolong'], 'title'));
				if(strlen($b['subtitle']) > 40)
					error(sprintf($config['error']['toolong'], 'subtitle'));
				
				if(!preg_match('/^\w+$/', $b['uri']))
					error(sprintf($config['error']['invalidfield'], 'URI'));
				
				if(openBoard($b['uri'])) {
					unset($board);
					error(sprintf($config['error']['boardexists'], sprintf($config['board_abbreviation'], $b['uri'])));
				}
				
				$query = prepare("INSERT INTO `boards` VALUES (NULL, :uri, :title, :subtitle)");
				$query->bindValue(':uri', $b['uri']);
				$query->bindValue(':title', $b['title']);
				if(!empty($b['subtitle'])) {
					$query->bindValue(':subtitle', $b['subtitle']);
				} else {
					$query->bindValue(':subtitle', null, PDO::PARAM_NULL);
				}
				$query->execute() or error(db_error($query));
				
				// Record the action
				modLog("Created a new board: {$b['title']}");
				
				// Open the board
				openBoard($b['uri']) or error("Couldn't open board after creation.");
				
				// Create the posts table
				query(Element('posts.sql', Array('board' => $board['uri']))) or error(db_error());
				
				// Build the board
				buildIndex();
				
				header('Location: ?/board/' . $board['uri'], true, $config['redirect_http']);
			} else {
				
				$body .= form_newBoard();
				
				// TODO: Statistics, etc, in the dashboard.
				
				echo Element('page.html', Array(
					'config'=>$config,
					'title'=>'New board',
					'body'=>$body,
					'mod'=>true
					)
				);
			}
		} elseif(preg_match('/^\/' . $regex['board'] . '(' . $regex['index'] . '|' . $regex['page'] . ')?$/', $query, $matches)) {
			// Board index
			
			$boardName = $matches[1];
			
			// Open board
			if(!openBoard($boardName))
				error($config['error']['noboard']);
			
			$page_no = empty($matches[2]) || $matches[2] == $config['file_index'] ? 1 : $matches[2];
			
			if(!$page = index($page_no, $mod)) {
				error($config['error']['404']);
			}
			
			$page['pages'] = getPages(true);
			$page['pages'][$page_no-1]['selected'] = true;
			$page['btn'] = getPageButtons($page['pages'], true);
			$page['hidden_inputs'] = createHiddenInputs();
			$page['mod'] = true;
			
			echo Element('index.html', $page);
		} elseif(preg_match('/^\/' . $regex['board'] . $regex['res'] . $regex['page'] . '$/', $query, $matches)) {
			// View thread
			
			$boardName = $matches[1];
			$thread = $matches[2];
			// Open board
			if(!openBoard($boardName))
				error($config['error']['noboard']);
			
			$page = buildThread($thread, true, $mod);
			
			echo $page;
		} elseif(preg_match('/^\/' . $regex['board'] . 'deletefile\/(\d+)$/', $query, $matches)) {
			if($mod['type'] < $config['mod']['deletefile']) error($config['error']['noaccess']);
			// Delete file from post
			
			$boardName = $matches[1];
			$post = $matches[2];
			// Open board
			if(!openBoard($boardName))
				error($config['error']['noboard']);
			
			// Delete post
			deleteFile($post);
			
			// Record the action
			modLog("Removed file from post #{$post}");
			
			// Rebuild board
			buildIndex();
			
			
			// Redirect
			if(isset($_SERVER['HTTP_REFERER']))
				header('Location: ' . $_SERVER['HTTP_REFERER'], true, $config['redirect_http']);
			else
				header('Location: ?/' . sprintf($config['board_path'], $boardName) . $config['file_index'], true, $config['redirect_http']);
		} elseif(preg_match('/^\/' . $regex['board'] . 'delete\/(\d+)$/', $query, $matches)) {
			if($mod['type'] < $config['mod']['delete']) error($config['error']['noaccess']);
			// Delete post
			
			$boardName = $matches[1];
			$post = $matches[2];
			// Open board
			if(!openBoard($boardName))
				error($config['error']['noboard']);
			
			// Delete post
			deletePost($post);
			
			// Record the action
			modLog("Deleted post #{$post}");
			
			// Rebuild board
			buildIndex();
			
			// Redirect
			if(isset($_SERVER['HTTP_REFERER']))
				header('Location: ' . $_SERVER['HTTP_REFERER'], true, $config['redirect_http']);
			else
				header('Location: ?/' . sprintf($config['board_path'], $boardName) . $config['file_index'], true, $config['redirect_http']);
		} elseif(preg_match('/^\/' . $regex['board'] . '(un)?sticky\/(\d+)$/', $query, $matches)) {
			if($mod['type'] < $config['mod']['sticky']) error($config['error']['noaccess']);
			// Add/remove sticky
			
			$boardName = $matches[1];
			$post = $matches[3];
			// Open board
			if(!openBoard($boardName))
				error($config['error']['noboard']);
			
			$query = prepare(sprintf("UPDATE `posts_%s` SET `sticky` = :sticky WHERE `id` = :id AND `thread` IS NULL", $board['uri']));
			$query->bindValue(':id', $post, PDO::PARAM_INT);
			
			if($matches[2] == 'un') {
				// Record the action
				modLog("Unstickied post #{$post}");
				$query->bindValue(':sticky', 0, PDO::PARAM_INT);
			} else {
				// Record the action
				modLog("Stickied post #{$post}");
				$query->bindValue(':sticky', 1, PDO::PARAM_INT);
			}
			
			$query->execute() or error(db_error($query));
			
			buildIndex();
			buildThread($post);
			
			
			// Redirect
			if(isset($_SERVER['HTTP_REFERER']))
				header('Location: ' . $_SERVER['HTTP_REFERER'], true, $config['redirect_http']);
			else
				header('Location: ?/' . sprintf($config['board_path'], $boardName) . $config['file_index'], true, $config['redirect_http']);
		} elseif(preg_match('/^\/' . $regex['board'] . '(un)?lock\/(\d+)$/', $query, $matches)) {
			if($mod['type'] < $config['mod']['lock']) error($config['error']['noaccess']);
			// Lock/Unlock
			
			$boardName = $matches[1];
			$post = $matches[3];
			// Open board
			if(!openBoard($boardName))
				error($config['error']['noboard']);
			
			$query = prepare(sprintf("UPDATE `posts_%s` SET `locked` = :locked WHERE `id` = :id AND `thread` IS NULL", $board['uri']));
			$query->bindValue(':id', $post, PDO::PARAM_INT);
			
			if($matches[2] == 'un') {
				// Record the action
				modLog("Unlocked post #{$post}");
				$query->bindValue(':locked', 0, PDO::PARAM_INT);
			} else {
				// Record the action
				modLog("Locked post #{$post}");
				$query->bindValue(':locked', 1, PDO::PARAM_INT);
			}
			
			$query->execute() or error(db_error($query));
			
			buildIndex();
			buildThread($post);
			
			
			// Redirect
			if(isset($_SERVER['HTTP_REFERER']))
				header('Location: ' . $_SERVER['HTTP_REFERER'], true, $config['redirect_http']);
			else
				header('Location: ?/' . sprintf($config['board_path'], $boardName) . $config['file_index'], true, $config['redirect_http']);
		} elseif(preg_match('/^\/' . $regex['board'] . 'deletebyip\/(\d+)$/', $query, $matches)) {
			// Delete all posts by an IP
			
			$boardName = $matches[1];
			$post = $matches[2];
			// Open board
			if(!openBoard($boardName))
				error($config['error']['noboard']);
			
			$query = prepare(sprintf("SELECT `ip` FROM `posts_%s` WHERE `id` = :id", $board['uri']));
			$query->bindValue(':id', $post);
			$query->execute() or error(db_error($query));
			
			if(!$post = $query->fetch())
				error($config['error']['invalidpost']);
			
			$ip = $post['ip'];
			
			// Record the action
			modLog("Deleted all posts by IP address: {$ip}");
			
			$query = prepare(sprintf("SELECT `id` FROM `posts_%s` WHERE `ip` = :ip", $board['uri']));
			$query->bindValue(':ip', $ip);
			$query->execute() or error(db_error($query));
			
			if($query->rowCount() < 1)
				error($config['error']['invalidpost']);
			
			while($post = $query->fetch()) {
				deletePost($post['id'], false);
			}
			
			buildIndex();
			
			if(isset($_SERVER['HTTP_REFERER']))
				header('Location: ' . $_SERVER['HTTP_REFERER'], true, $config['redirect_http']);
			else
				header('Location: ?/' . sprintf($config['board_path'], $boardName) . $config['file_index'], true, $config['redirect_http']);
		} elseif(preg_match('/^\/ban$/', $query)) {
			// Ban page
			
			if(isset($_POST['new_ban'])) {
				if(	!isset($_POST['ip']) ||
					!isset($_POST['reason']) ||
					!isset($_POST['length'])
				)	error($config['error']['missedafield']);
				
				// Check required fields
				if(empty($_POST['ip']))
					error(sprintf($config['error']['required'], 'IP address'));
				
				$query = prepare("INSERT INTO `bans` VALUES (:ip, :mod, :set, :expires, :reason)");
				
				// 1yr2hrs30mins
				// 1y2h30m
				$expire = 0;
				if(preg_match('/^((\d+)\s?ye?a?r?s?)?\s?+((\d+)\s?mon?t?h?s?)?\s?+((\d+)\s?we?e?k?s?)?\s?+((\d+)\s?da?y?s?)?((\d+)\s?ho?u?r?s?)?\s?+((\d+)\s?mi?n?u?t?e?s?)?\s?+((\d+)\s?se?c?o?n?d?s?)?$/', $_POST['length'], $m)) {
					if(isset($m[2])) {
						// Years
						$expire += $m[2]*60*60*24*365;
					}
					if(isset($m[4])) {
						// Months
						$expire += $m[4]*60*60*24*30;
					}
					if(isset($m[6])) {
						// Weeks
						$expire += $m[6]*60*60*24*7;
					}
					if(isset($m[8])) {
						// Days
						$expire += $m[8]*60*60*24;
					}
					if(isset($m[10])) {
						// Hours
						$expire += $m[10]*60*60;
					}
					if(isset($m[12])) {
						// Minutes
						$expire += $m[12]*60;
					}
					if(isset($m[14])) {
						// Seconds
						$expire += $m[14];
					}
				}
				if($expire) {
					$query->bindValue(':expires', time()+$expire, PDO::PARAM_INT);
				} else {
					// Never expire
					$query->bindValue(':expires', null, PDO::PARAM_NULL);
				}
				
				$query->bindValue(':ip', $_POST['ip'], PDO::PARAM_STR);
				$query->bindValue(':mod', $mod['id'], PDO::PARAM_INT);
				$query->bindValue(':set', time(), PDO::PARAM_INT);
				
				if(isset($_POST['reason'])) {
					$query->bindValue(':reason', $_POST['reason'], PDO::PARAM_STR);
				} else {
					$query->bindValue(':reason', null, PDO::PARAM_NULL);
				}
				
				// Record the action
				modLog('Created a ' . ($expire ? $expire . ' second' : 'permanent') . " ban for {$_POST['ip']} with " . (!empty($_POST['reason']) ? "reason \"{$_POST['reason']}\"" : 'no reason'));
				
				$query->execute() or error(db_error($query));
				
				// Delete too
				if($mod['type'] >= $config['mod']['delete'] && isset($_POST['delete']) && isset($_POST['board'])) {
					openBoard($_POST['board']);
					
					$post = round($_POST['delete']);
					
					deletePost($post);
					
					// Record the action
					modLog("Deleted post #{$post}");
					
					// Rebuild board
					buildIndex();
				}
				
				// Redirect
				if(isset($_POST['continue']))
					header('Location: ' . $_POST['continue'], true, $config['redirect_http']);
				elseif(isset($board))
					header('Location: ?/' . sprintf($config['board_path'], $boardName) . $config['file_index'], true, $config['redirect_http']);
				elseif(isset($_SERVER['HTTP_REFERER']))
					header('Location: ' . $_SERVER['HTTP_REFERER'], true, $config['redirect_http']);
				else
					header('Location: ?/', true, $config['redirect_http']);
			}
		} elseif(preg_match('/^\/' . $regex['board'] . 'ban(&delete)?\/(\d+)$/', $query, $matches)) {
			if($mod['type'] < $config['mod']['delete']) error($config['error']['noaccess']);
			// Ban by post
			
			$boardName = $matches[1];
			$delete = isset($matches[2]) && $matches[2] == '&delete';
			$post = $matches[3];
			// Open board
			if(!openBoard($boardName))
				error($config['error']['noboard']);
			
			$query = prepare(sprintf("SELECT `ip`,`id` FROM `posts_%s` WHERE `id` = :id LIMIT 1", $board['uri']));
			$query->bindValue(':id', $post, PDO::PARAM_INT);
			$query->execute() or error(db_error($query));
			
			if($query->rowCount() < 1) {
				error($config['error']['invalidpost']);
			}
		
			$post = $query->fetch();
			
			$body = form_newBan($post['ip'], null, isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : false, $delete ? $post['id'] : false, $delete ? $boardName : false);
			
			echo Element('page.html', Array(
				'config'=>$config,
				'title'=>'New ban',
				'body'=>$body,
				'mod'=>true
				)
			);
		} elseif(preg_match('/^\/IP\/(\d+\.\d+\.\d+\.\d+|' . $config['ipv6_regex'] . ')$/', $query, $matches)) {
			// View information on an IP address
			
			$ip = $matches[1];
			$host = $config['mod']['dns_lookup'] ? gethostbyaddr($ip) : false;
			
			if($mod['type'] >= $config['mod']['unban'] && isset($_POST['unban'])) {
				$query = prepare("DELETE FROM `bans` WHERE `ip` = :ip");
				$query->bindValue(':ip', $ip);
				$query->execute() or error(db_error($query));
			}
				
			
			$body = '';
			$boards = listBoards();
			foreach($boards as &$_board) {
				openBoard($_board['uri']);
				
				$temp = '';
				$query = prepare(sprintf("SELECT * FROM `posts_%s` WHERE `ip` = :ip ORDER BY `sticky` DESC, `time` DESC LIMIT :limit", $_board['uri']));
				$query->bindValue(':ip', $ip);
				$query->bindValue(':limit', $config['mod']['ip_recentposts'], PDO::PARAM_INT);
				$query->execute() or error(db_error($query));
				
				while($post = $query->fetch()) {
					if(!$post['thread']) {
						$po = new Thread($post['id'], $post['subject'], $post['email'], $post['name'], $post['trip'], $post['body'], $post['time'], $post['thumb'], $post['thumbwidth'], $post['thumbheight'], $post['file'], $post['filewidth'], $post['fileheight'], $post['filesize'], $post['filename'], $post['ip'], $post['sticky'], $post['locked'], '?/', $mod, false);
					} else {
						$po = new Post($post['id'], $post['thread'], $post['subject'], $post['email'], $post['name'], $post['trip'], $post['body'], $post['time'], $post['thumb'], $post['thumbwidth'], $post['thumbheight'], $post['file'], $post['filewidth'], $post['fileheight'], $post['filesize'], $post['filename'], $post['ip'], '?/', $mod);
					}
					$temp .= $po->build(true) . '<hr/>';
				}
				
				if(!empty($temp))
					$body .= '<fieldset><legend>Last ' . $query->rowCount() . ' posts on <a href="?/' .
							sprintf($config['board_path'], $_board['uri']) . $config['file_index'] .
						'">' .
						sprintf($config['board_abbreviation'], $_board['uri']) . ' - ' . $_board['title'] .
						'</a></legend>' . $temp . '</fieldset>';
			}
			
			if($mod['type'] >= $config['mod']['view_ban']) {
				$query = prepare("SELECT * FROM `bans` INNER JOIN `mods` ON `mod` = `id` WHERE `ip` = :ip");
				$query->bindValue(':ip', $ip);
				$query->execute() or error(db_error($query));
				
				if($query->rowCount() > 0) {
					$body .= '<fieldset><legend>Ban' . ($query->rowCount() == 1 ? '' : 's') . ' on record</legend><form action="" method="post" style="text-align:center">';
					
					while($ban = $query->fetch()) {
						$body .= '<table style="width:400px;margin-bottom:10px;border-bottom:1px solid #ddd;padding:5px"><tr><th>Status</th><td>' . 
							($config['mod']['view_banexpired'] && $ban['expires'] != 0 && $ban['expires'] < time() ?
								'Expired'
							: 'Active') .
						'</td></tr>' .
						
						// IP
						'<tr><th>IP</th><td>' . $ban['ip'] . '</td></tr>' .
						
						// Reason
						'<tr><th>Reason</th><td>' . $ban['reason'] . '</td></tr>' .
						
						// Set
						'<tr><th>Set</th><td>' . date($config['post_date'], $ban['set']) . '</td></tr>' .
						
						// Expires
						'<tr><th>Expires</th><td>' . 
							($ban['expires'] == 0 ?
								'<em>Never</em>'
							:
								date($config['post_date'], $ban['expires'])
							) .
						'</td></tr>' .
						
						// Staff
						'<tr><th>Staff</th><td>' .
							($mod['type'] < $config['mod']['view_banstaff'] ?
								($config['mod']['view_banquestionmark'] ?
									'?'
								:
									($ban['type'] == JANITOR ? 'Janitor' :
									($ban['type'] == MOD ? 'Mod' :
									($ban['type'] == ADMIN ? 'Admin' :
									'?')))
								)
							:
								$ban['username']
							) .
						'</td></tr>' .
						
						'</tr></table>';
					}
					
					$body .= '<input type="submit" name="unban" value="Remove ban' . ($query->rowCount() == 1 ? '' : 's') . '" ' .
						($mod['type'] < $config['mod']['unban'] ? 'disabled' : '') .
					'/></form></fieldset>';
				}
			}
			
			if($mod['type'] >= $config['mod']['ip_banform'])
				$body .= form_newBan($ip, null, isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : false);
			
			echo Element('page.html', Array(
				'config'=>$config,
				'title'=>'IP: ' . $ip,
				'subtitle' => $host,
				'body'=>$body,
				'mod'=>true
				)
			);
		} else {
			error($config['error']['404']);
		}
	}
	
	// Close the connection in-case it's still open
	sql_close();
?>

