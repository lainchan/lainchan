<?php
	require 'inc/functions.php';
	require 'inc/display.php';
	if (file_exists('inc/instance-config.php')) {
		require 'inc/instance-config.php';
	}
	require 'inc/config.php';
	require 'inc/template.php';
	require 'inc/user.php';
	
	// If not logged in
	if(!$user) {
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
			
			// Close connection
			sql_close();
		} else {
			loginForm();
		}
	} else {
		$query = $_SERVER['QUERY_STRING'];
		$regex = Array(
			'board' => str_replace('%s', '\w{1,8}', preg_quote(BOARD_PATH, '/'))
		);
		
		// Dashboard
		if(preg_match('/^\/?$/', $query)) {
			
			
		// Board index
		} elseif(preg_match('/^\/' . $regex['board'] . '(' . preg_quote(FILE_INDEX, '/') . ')?$/', $query)) {
			
			
		} else {
				error("Page not found.");
		}
		// The rest is not done yet...
	}
?>

