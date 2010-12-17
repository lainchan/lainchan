<?php
	require 'inc/functions.php';
	require 'inc/display.php';
	if (file_exists('inc/instance-config.php')) {
		require 'inc/instance-config.php';
	}
	require 'inc/config.php';
	require 'inc/template.php';
	require 'inc/user.php';
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
			
			// Open connection
			sql_open();
			
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
				if(MOD_NEVER_REAL_PASSWORD && $name == 'MY_PASSWORD')
					$value = '<em>hidden</em>';
				else {
					// For some reason PHP is only giving me the first defined value (the default), so use constant()
					$value = constant($name);
					if(gettype($value) == 'boolean') {
						$value = $value ? '<span style="color:green;">On</span>' : '<span style="color:red;">Off</span>';
					} elseif(gettype($value) == 'string') {
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
				
				mysql_query(sprintf(
					"INSERT INTO `boards` VALUES (NULL, '%s', '%s', " .
							(empty($b['subtitle']) ? 'NULL' :  "'%s'" ) .
					")",
						mysql_real_escape_string($b['uri']),
						mysql_real_escape_string($b['title']),
						mysql_real_escape_string($b['subtitle'])
				), $sql) or error(mysql_error($sql));
				
				// Open the board
				openBoard($b['uri']) or error("Couldn't open board after creation.");
				
				// Create the posts table
				mysql_query(Element('posts.sql', Array('board' => $board['uri'])), $sql) or error(mysql_error($sql));
				
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
			
			if(!$page = index(empty($matches[2]) || $matches[2] == FILE_INDEX ? 1 : $matches[2], true)) {
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
			
			$page = buildThread($thread, true, true);
			
			echo $page;
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
			
		} else {
			error(ERROR_404);
		}
	}
	
	// Close the connection in-case it's still open
	sql_close();
?>

