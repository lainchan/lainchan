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
		$query = $_SERVER['QUERY_STRING'];
		$regex = Array(
			'board' => str_replace('%s', '(\w{1,8})', preg_quote(BOARD_PATH, '/'))
		);
		
		if(preg_match('/^\/?$/', $query)) {
			// Dashboard
			
			// Body
			$body = '';
			
			$body .= 	'<fieldset><legend>Boards</legend>' . 
						ulBoards() . 
						'</fieldset>';
			
			die(Element('page.html', Array(
				'index'=>ROOT,
				'title'=>'Dashboard',
				'body'=>$body
				)
			));
		} elseif(preg_match('/^\/' . $regex['board'] . '(' . preg_quote(FILE_INDEX, '/') . ')?$/', $query, $matches)) {
			// Board index
			
			$boardName = $matches[1];
			// Open board
			openBoard($boardName);
			
			echo Element('index.html', index(1));		
			
		} else {
			error("Page not found.");
		}
	}
	
	// Close the connection in-case it's still open
	sql_close();
?>

