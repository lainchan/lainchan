<?php
	define('IS_INSTALLATION',	true);
	
	require 'inc/functions.php';
	require 'inc/display.php';
	if (file_exists('inc/instance-config.php')) {
		require 'inc/instance-config.php';
	}
	require 'inc/config.php';
	require 'inc/template.php';
	require 'inc/user.php';
	
	function image($type) {
		return "<img src=\"static/{$type}.png\" style=\"margin:0px;width:16px;height:16px;\" />";
	}
	function check($title, $test) {
		global $body, $count;
		$count[$test]++;
		$body .= '<tr><td style="width:100%;border-bottom:1px solid #DDD;">' . $title . '</td><td style="width:1%;white-space:nowrap;border-bottom:1px solid #DDD;">' . image($test) . '</td></tr>';
	}
	function title($text) {
		global $body;
		$body .= '<tr><td colspan="2" style="padding-top:15px;font-weight:bold;width:100%;border-bottom:1px solid #DDD;">' . $text . '</td></tr>';
	}
	
	$count = Array('ok'=>0, 'warning'=>0, 'error'=>0);
	$todo = Array();
	$body = '<table style="width:600px;margin:auto;">';
	
	$extensions = Array('mysql', 'gd');
	
	// Extensions
	title('Extensions');
	foreach($extensions as &$ext) {
		if(extension_loaded($ext)) {
			$body .= check($ext, 'ok');
		} else {
			$body .= check($ext, 'error');
			$todo[] = 'Install module "' . $ext . '"';
		}
	}
	
	// Database
	title('Database');
	
	if(extension_loaded('mysql')) {
		if($sql = @mysql_connect(MY_SERVER, MY_USER, MY_PASSWORD)) {
			$body .= check('Connection to server.', 'ok');
			if(@mysql_select_db(MY_DATABASE, $sql))
				$body .= check('Select database.', 'ok');
			else {
				$body .= check('Select database.', 'error');
				$todo[] = 'instance-config.php: Check database configuration.';
			}
		} else {
			$body .= check('Connection to server.', 'error');
			$todo[] = 'instance-config.php: Check database configuration.';
		}
	}
	
	// Configuration
	title('Configuration');
	$root = dirname($_SERVER['REQUEST_URI']) . (dirname($_SERVER['REQUEST_URI']) == '/' ? '' : '/');
	if(ROOT != $root) {
		$body .= check('Correct document root.', 'error');
		$todo[] = "instance-config.php: Change ROOT to '{$root}'";
	} else
		$body .= check('Correct document root.', 'ok');
	
	// Permissions
	title('Permissions');
	
	$directories = Array(DIR_IMG, DIR_THUMB, DIR_RES, '.');
	foreach($directories as $dir) {
		if(file_exists($dir)) {
			if(is_writable($dir) && is_readable($dir)) {
				$body .= check($dir, 'ok');
			} else {
				$body .= check($dir, 'error');
				$todo[] = 'CHMOD ' . $dir . ' to allow PHP to read and write.';
			}
		} else {
			$body .= check($dir, 'error');
			$todo[] = 'Create directory: ' . $dir;
		}
	}
	
	// Other
	title('Other');
	if(get_magic_quotes_gpc()) {
		$body .= check('magic_quotes_gpc', 'warning');
		$todo[] = 'Recommended: Disable magic_quotes_gpc in your PHP configuration.';
	} else
		$body .= check('magic_quotes_gpc', 'ok');
	
	$body .= '</table>';
	
	if(!empty($todo)) {
		$body .= '<pre style="width:600px;margin:20px auto;">';
		foreach($todo as $do)
			$body .= "{$do}\n";
		$body .= '</pre>';
	}
	
	if(!$count['error']) {
		$body .= '<p style="text-align:center;">Everything seems okay.</p>';
	}
	
	$body .= '<p style="text-align:center;font-weight:bold;">Disregard me. I haven\'t been patched to work with multiple boards yet.</p>';
	
	die(Element('page.html', Array('index' => ROOT, 'title'=>'Tinyboard', 'subtitle'=>'Installation', 'body'=>$body)));
?>