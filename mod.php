<?php
	require 'inc/functions.php';
	require 'inc/display.php';
	if (file_exists('inc/instance-config.php')) {
		require 'inc/instance-config.php';
	}
	require 'inc/config.php';
	require 'inc/template.php';
	require 'inc/database.php';
	require 'inc/user.php';
	
	sql_open();
	
	// Check if banned
	checkBan();
			
	require 'inc/mod.php';
	
	// Fix some encoding issues
	header('Content-Type: text/html; charset=utf-8', true);
	
	// If not logged in
	if(!$mod) {
		if(isset($_POST['login'])) {
			// Check if inputs are set and not empty
			if(	!isset($_POST['username']) ||
				!isset($_POST['password']) ||
				empty($_POST['username']) ||
				empty($_POST['password'])
				) loginForm(ERROR_INVALID, $_POST['username']);
			
			
			if(!login($_POST['username'], $_POST['password']))
				loginForm(ERROR_INVALID, $_POST['username']);
			
			// Login successful
			// Set cookies
			setCookies();
			
			// Redirect
			header('Location: ?' . MOD_DEFAULT, true, REDIRECT_HTTP);
			
			// Close connection
			sql_close();
		} else {
			loginForm();
		}
	} else {
		$query = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
		
		// A sort of "cache"
		// Stops calling preg_quote and str_replace when not needed; only does it once
		$regex = Array(
			'board' => str_replace('%s', '(\w{1,8})', preg_quote(BOARD_PATH, '/')),
			'page' => str_replace('%d', '(\d+)', preg_quote(FILE_PAGE, '/')),
			'img' => preg_quote(DIR_IMG, '/'),
			'thumb' => preg_quote(DIR_THUMB, '/'),
			'res' => preg_quote(DIR_RES, '/'),
			'index' => preg_quote(FILE_INDEX, '/')
		);
		
		if(preg_match('/^\/?$/', $query)) {
			// Dashboard
			$fieldset = Array(
				'Boards' => '',
				'Administration' => ''
			);
			
			// Boards
			$fieldset['Boards'] .= ulBoards();
			
			if($mod['type'] >= MOD_SHOW_CONFIG) {
				$fieldset['Administration'] .= 	'<li><a href="?/config">Show configuration</a></li>';
			}
			
			// TODO: Statistics, etc, in the dashboard.
			
			$body = '';
			foreach($fieldset as $title => $data) {
				if($data)
					$body .= "<fieldset><legend>{$title}</legend><ul>{$data}</ul></fieldset>";
			}
			
			echo Element('page.html', Array(
				'index'=>ROOT,
				'title'=>'Dashboard',
				'body'=>$body
				//,'mod'=>true /* All 'mod' does, at this point, is put the "Return to dashboard" link in. */
				)
			);
		} elseif(preg_match('/^\/config$/', $query)) {
			if($mod['type'] < MOD_SHOW_CONFIG) error(ERROR_NOACCESS);
			
			// Show instance-config.php
			
			//$data = highlight_file('inc/instance-config.php', true);
			//if(MOD_NEVER_REAL_PASSWORD) {
			//	// Rough and dirty removal of password
			//	$data = str_replace(MY_PASSWORD, '*******', $data);
			//}
			
			$constants = get_defined_constants(true);
			$constants = $constants['user'];
			
			$data = '';
			foreach($constants as $name => $value) {
				if(MOD_NEVER_REAL_PASSWORD && $name == 'DB_PASSWORD')
					$value = '<em>hidden</em>';
				else {
					// For some reason PHP is only giving me the first defined value (the default), so use constant()
					$value = constant($name);
					if(gettype($value) == 'boolean') {
						$value = $value ? '<span style="color:green;">On</span>' : '<span style="color:red;">Off</span>';
					} elseif(gettype($value) == 'string') {
						if(empty($value))
							$value = '<em>empty</em>';
						else
							$value = '<span style="color:maroon;">' . utf8tohtml(substr($value, 0, 110) . (strlen($value) > 110 ? '…' : '')) . '</span>';
					} elseif(gettype($value) == 'integer') {
						// Show permissions in a cleaner way
						if(preg_match('/^MOD_/', $name) && $name != 'MOD_JANITOR' &&  $name != 'MOD_MOD' &&  $name != 'MOD_ADMIN') {
							if($value == MOD_JANITOR)
								$value = 'Janitor';
							elseif($value == MOD_MOD)
								$value = 'Mod';
							elseif($value == MOD_ADMIN)
								$value = 'Admin';
						}
						$value = '<span style="color:black;">' . $value . '</span>';
					}
				}
				
				$data .= 
					'<tr><th style="text-align:left;">' . 
						$name .
					'</th><td>' .
						$value .
					'</td></tr>';
			}
			
			$body = '<fieldset><legend>Configuration</legend><table>' . $data . '</table></fieldset>';
			
			echo Element('page.html', Array(
				'index'=>ROOT,
				'title'=>'Configuration',
				'body'=>$body,
				'mod'=>true
				)
			);
		} elseif(preg_match('/^\/new$/', $query)) {
			if($mod['type'] < MOD_NEWBOARD) error(ERROR_NOACCESS);
			
			// New board
			$body = '';
			
			if(isset($_POST['new_board'])) {
				// Create new board
				if(	!isset($_POST['uri']) ||
					!isset($_POST['title']) ||
					!isset($_POST['subtitle'])
				)	error(ERROR_MISSEDAFIELD);
				
				$b = Array(
					'uri' => $_POST['uri'],
					'title' => $_POST['title'],
					'subtitle' => $_POST['subtitle']
				);
				
				// Check required fields
				if(empty($b['uri']))
					error(sprintf(ERROR_REQUIRED, 'URI'));
				if(empty($b['title']))
					error(sprintf(ERROR_REQUIRED, 'title'));
				
				// Check string lengths
				if(strlen($b['uri']) > 8)
					error(sprintf(ERROR_TOOLONG, 'URI'));
				if(strlen($b['title']) > 20)
					error(sprintf(ERROR_TOOLONG, 'title'));
				if(strlen($b['subtitle']) > 40)
					error(sprintf(ERROR_TOOLONG, 'subtitle'));
				
				if(!preg_match('/^\w+$/', $b['uri']))
					error(sprintf(ERROR_INVALIDFIELD, 'URI'));
				
				$query = prepare("INSERT INTO `boards` VALUES (NULL, :uri, :title, :subtitle)");
				$query->bindValue(':uri', $b['uri']);
				$query->bindValue(':title', $b['title']);
				if(!empty($b['subtitle'])) {
					$query->bindValue(':subtitle', $b['subtitle']);
				} else {
					$query->bindValue(':subtitle', null, PDO::PARAM_NULL);
				}
				$query->execute() or error(db_error($query));
				
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
				'index'=>ROOT,
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
				error(ERROR_NOBOARD);
			
			if(!$page = index(empty($matches[2]) || $matches[2] == FILE_INDEX ? 1 : $matches[2], $mod)) {
				error(ERROR_404);
			}
			$page['pages'] = getPages(true);
			$page['mod'] = true;
			
			echo Element('index.html', $page);
		} elseif(preg_match('/^\/' . $regex['board'] . $regex['res'] . $regex['page'] . '$/', $query, $matches)) {
			// View thread
			
			$boardName = $matches[1];
			$thread = $matches[2];
			// Open board
			if(!openBoard($boardName))
				error(ERROR_NOBOARD);
			
			$page = buildThread($thread, true, $mod);
			
			echo $page;
		} elseif(preg_match('/^\/' . $regex['board'] . 'deletefile\/(\d+)$/', $query, $matches)) {
			if($mod['type'] < MOD_DELETEFILE) error(ERROR_NOACCESS);
			// Delete file from post
			
			$boardName = $matches[1];
			$post = $matches[2];
			// Open board
			if(!openBoard($boardName))
				error(ERROR_NOBOARD);
			
			// Delete post
			deleteFile($post);
			// Rebuild board
			buildIndex();
			
			
			// Redirect
			if(isset($_SERVER['HTTP_REFERER']))
				header('Location: ' . $_SERVER['HTTP_REFERER'], true, REDIRECT_HTTP);
			else
				header('Location: ?/' . sprintf(BOARD_PATH, $boardName) . FILE_INDEX, true, REDIRECT_HTTP);
		} elseif(preg_match('/^\/' . $regex['board'] . 'delete\/(\d+)$/', $query, $matches)) {
			if($mod['type'] < MOD_DELETE) error(ERROR_NOACCESS);
			// Delete post
			
			$boardName = $matches[1];
			$post = $matches[2];
			// Open board
			if(!openBoard($boardName))
				error(ERROR_NOBOARD);
			
			// Delete post
			deletePost($post);
			// Rebuild board
			buildIndex();
			
			// Redirect
			if(isset($_SERVER['HTTP_REFERER']))
				header('Location: ' . $_SERVER['HTTP_REFERER'], true, REDIRECT_HTTP);
			else
				header('Location: ?/' . sprintf(BOARD_PATH, $boardName) . FILE_INDEX, true, REDIRECT_HTTP);
		} elseif(preg_match('/^\/' . $regex['board'] . '(un)?sticky\/(\d+)$/', $query, $matches)) {
			if($mod['type'] < MOD_STICKY) error(ERROR_NOACCESS);
			// Add/remove sticky
			
			$boardName = $matches[1];
			$post = $matches[3];
			// Open board
			if(!openBoard($boardName))
				error(ERROR_NOBOARD);
			
			$query = prepare(sprintf("UPDATE `posts_%s` SET `sticky` = :sticky WHERE `id` = :id AND `thread` IS NULL", $board['uri']));
			$query->bindValue(':id', $post, PDO::PARAM_INT);
			
			if($matches[2] == 'un') {
				$query->bindValue(':sticky', 0, PDO::PARAM_INT);
			} else {
				$query->bindValue(':sticky', 1, PDO::PARAM_INT);
			}
			
			$query->execute() or error(db_error($query));
			
			buildIndex();
			buildThread($post);
			
			
			// Redirect
			if(isset($_SERVER['HTTP_REFERER']))
				header('Location: ' . $_SERVER['HTTP_REFERER'], true, REDIRECT_HTTP);
			else
				header('Location: ?/' . sprintf(BOARD_PATH, $boardName) . FILE_INDEX, true, REDIRECT_HTTP);
		} elseif(preg_match('/^\/' . $regex['board'] . '(un)?lock\/(\d+)$/', $query, $matches)) {
			if($mod['type'] < MOD_LOCK) error(ERROR_NOACCESS);
			// Lock/Unlock
			
			$boardName = $matches[1];
			$post = $matches[3];
			// Open board
			if(!openBoard($boardName))
				error(ERROR_NOBOARD);
			
			$query = prepare(sprintf("UPDATE `posts_%s` SET `locked` = :locked WHERE `id` = :id AND `thread` IS NULL", $board['uri']));
			$query->bindValue(':id', $post, PDO::PARAM_INT);
			
			if($matches[2] == 'un') {
				$query->bindValue(':locked', 0, PDO::PARAM_INT);
			} else {
				$query->bindValue(':locked', 1, PDO::PARAM_INT);
			}
			
			$query->execute() or error(db_error($query));
			
			buildIndex();
			buildThread($post);
			
			
			// Redirect
			if(isset($_SERVER['HTTP_REFERER']))
				header('Location: ' . $_SERVER['HTTP_REFERER'], true, REDIRECT_HTTP);
			else
				header('Location: ?/' . sprintf(BOARD_PATH, $boardName) . FILE_INDEX, true, REDIRECT_HTTP);
		} elseif(preg_match('/^\/' . $regex['board'] . 'ban\/(\d+)$/', $query, $matches)) {
			if($mod['type'] < MOD_DELETE) error(ERROR_NOACCESS);
			// Ban by post
			
			$boardName = $matches[1];
			$post = $matches[2];
			// Open board
			if(!openBoard($boardName))
				error(ERROR_NOBOARD);
			
			$query = prepare(sprintf("SELECT `ip`,`id` FROM `posts_%s` WHERE `id` = :id LIMIT 1", $board['uri']));
			$query->bindValue(':id', $post, PDO::PARAM_INT);
			$query->execute() or error(db_error($query));
			
			if($query->rowCount() < 1) {
				error(ERROR_INVALIDPOST);
			}
		
			$post = $query->fetch();
			
			if(isset($_POST['new_ban'])) {
				if(	!isset($_POST['ip']) ||
					!isset($_POST['reason']) ||
					!isset($_POST['length'])
				)	error(ERROR_MISSEDAFIELD);
				
				// Check required fields
				if(empty($_POST['ip']))
					error(sprintf(ERROR_REQUIRED, 'IP address'));
				
				$query = prepare("INSERT INTO `bans` VALUES (:ip, :mod, :set, :expires, :reason)");
				
				// 1yr2hrs30mins
				// 1y2h30m
				$expire = 0;
				if(preg_match('/^((\d+)\s?ye?a?r?s?)?\s?+((\d+)\s?we?e?k?s?)?\s?+((\d+)\s?da?y?s?)?((\d+)\s?ho?u?r?s?)?\s?+((\d+)?mi?n?u?t?e?s?)?\s?+((\d+)\s?se?c?o?n?d?s?)?$/', $_POST['length'], $m)) {
					if(isset($m[2])) {
						// Years
						$expire += $m[2]*60*60*24*7*52;
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
				$query->execute() or error(db_error($query));
				
				// Redirect
				if(isset($_POST['continue']))
					header('Location: ' . $_POST['continue'], true, REDIRECT_HTTP);
				else
					header('Location: ?/' . sprintf(BOARD_PATH, $boardName) . FILE_INDEX, true, REDIRECT_HTTP);				
			}
			
			$body = form_newBan($post['ip'], null, isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : false);
			
			echo Element('page.html', Array(
				'index'=>ROOT,
				'title'=>'New ban',
				'body'=>$body,
				'mod'=>true
				)
			);
		} else {
			error(ERROR_404);
		}
	}
	
	// Close the connection in-case it's still open
	sql_close();
?>

