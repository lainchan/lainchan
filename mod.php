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
				'Administration' => ''
			);
			
			// Boards
			$fieldset['Boards'] .= ulBoards();
			
			if($mod['type'] >= $config['mod']['view_banlist']) {
				$fieldset['Administration'] .= 	'<li><a href="?/bans">Ban list</a></li>';
			}
			if($mod['type'] >= $config['mod']['show_config']) {
				$fieldset['Administration'] .= 	'<li><a href="?/config">Show configuration</a></li>';
			}
			
			// TODO: Statistics, etc, in the dashboard.
			
			$body = '';
			foreach($fieldset as $title => $data) {
				if($data)
					$body .= "<fieldset><legend>{$title}</legend><ul>{$data}</ul></fieldset>";
			}
			
			echo Element('page.html', Array(
				'index'=>$config['root'],
				'title'=>'Dashboard',
				'body'=>$body
				//,'mod'=>true /* All 'mod' does, at this point, is put the "Return to dashboard" link in. */
				)
			);
		} elseif(preg_match('/^\/bans$/', $query)) {
			if($mod['type'] < $config['mod']['view_banlist']) error($config['error']['noaccess']);
			
			if($config['mod']['view_banexpired']) {
				$query = prepare("SELECT * FROM `bans` INNER JOIN `mods` ON `mod` = `id` GROUP BY `ip` ORDER BY `expires` < :time, `set` DESC");
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
				$body .= '<table><tr><th>IP address</th><th>Reason</th><th>Set</th><th>Expires</th><th>Staff</th><th>Actions</th></tr>';
				
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
					'<td>' . $ban['reason'] . '</td>' .
					
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
					
					'<td></td>' .
					
					'</tr>';
				}
				
				$body .= '</table></form>';
			}
			
			echo Element('page.html', Array(
				'index'=>$config['root'],
				'title'=>'Ban list',
				'body'=>$body,
				'mod'=>true
			)
		);
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
				'index'=>$config['root'],
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
			}
			
			$body .= form_newBoard();
			
			// TODO: Statistics, etc, in the dashboard.
			
			echo Element('page.html', Array(
				'index'=>$config['root'],
				'title'=>'New board',
				'body'=>$body,
				'mod'=>true
				)
			);
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
			modLog("Deleted all posts by IP address: #{$ip}");
			
			$query = prepare(sprintf("SELECT `id` FROM `posts_%s` WHERE `ip` = :ip", $board['uri']));
			$query->bindValue(':ip', $ip);
			$query->execute() or error(db_error($query));
			
			if($query->rowCount() < 1)
				error($config['error']['invalidpost']);
			
			while($post = $query->fetch()) {
				deletePost($post['id'], false);
			}
			
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
				if(preg_match('/^((\d+)\s?ye?a?r?s?)?\s?+((\d+)\s?we?e?k?s?)?\s?+((\d+)\s?da?y?s?)?((\d+)\s?ho?u?r?s?)?\s?+((\d+)\s?mi?n?u?t?e?s?)?\s?+((\d+)\s?se?c?o?n?d?s?)?$/', $_POST['length'], $m)) {
					if(isset($m[2])) {
						// Years
						$expire += $m[2]*60*60*24*365;
					}
					if(isset($m[4])) {
						// Weeks
						$expire += $m[4]*60*60*24*7;
					}
					if(isset($m[6])) {
						// Days
						$expire += $m[6]*60*60*24;
					}
					if(isset($m[8])) {
						// Hours
						$expire += $m[8]*60*60;
					}
					if(isset($m[10])) {
						// Minutes
						$expire += $m[10]*60;
					}
					if(isset($m[12])) {
						// Seconds
						$expire += $m[12];
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
				modLog("Created a ban for {$_POST['ip']} with reason {$_POST['reason']}");
				
				$query->execute() or error(db_error($query));
				
				// Delete too
				if($mod['type'] >= $config['mod']['delete'] && isset($_POST['delete']) && isset($_POST['board'])) {
					openBoard($_POST['board']);
					deletePost(round($_POST['delete']));
				}
				
				// Redirect
				if(isset($_POST['continue']))
					header('Location: ' . $_POST['continue'], true, $config['redirect_http']);
				else
					header('Location: ?/' . sprintf($config['board_path'], $boardName) . $config['file_index'], true, $config['redirect_http']);				
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
				'index'=>$config['root'],
				'title'=>'New ban',
				'body'=>$body,
				'mod'=>true
				)
			);
		} elseif(preg_match('/^\/IP\/(\d+\.\d+\.\d+\.\d+|' . $config['ipv6_regex'] . ')$/', $query, $matches)) {
			// View information on an IP address
			
			$ip = $matches[1];
			$host = $config['mod']['dns_lookup'] ? gethostbyaddr($ip) : false;
			
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
					$po = new Post($post['id'], $post['thread'], $post['subject'], $post['email'], $post['name'], $post['trip'], $post['body'], $post['time'], $post['thumb'], $post['thumbwidth'], $post['thumbheight'], $post['file'], $post['filewidth'], $post['fileheight'], $post['filesize'], $post['filename'], $post['ip'], $mod ? '?/' : $config['root'], $mod);
					$temp .= $po->build();
				}
				if(!empty($temp))
					$body .= '<fieldset><legend>Last ' . $query->rowCount() . ' posts on <a href="?/' .
							sprintf($config['board_path'], $_board['uri']) . $config['file_index'] .
						'">' .
						sprintf($config['board_abbreviation'], $_board['uri']) . ' - ' . $_board['title'] .
						'</a></legend>' . $temp . '</fieldset>';
			}
			
			if($config['mod']['ip_banform'])
				$body .= form_newBan($ip, null, isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : false);
			
			echo Element('page.html', Array(
				'index'=>$config['root'],
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

