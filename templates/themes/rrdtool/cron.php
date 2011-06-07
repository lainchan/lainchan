<?php
	$_SERVER = Array('REQUEST_URI' => '', 'HTTP_HOST' => '', 'SCRIPT_FILENAME' => '');
	chdir(str_replace('\\', '/', dirname(__FILE__)) . '/../../../');
	
	require 'inc/functions.php';
	require 'inc/display.php';
	require 'inc/template.php';
	require 'inc/database.php';
	
	require 'theme.php';
	rebuildTheme('rrdtool', 'cron');
?>