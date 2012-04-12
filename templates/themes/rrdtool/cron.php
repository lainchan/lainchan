<?php
	$_SERVER = Array('REQUEST_URI' => '', 'HTTP_HOST' => '', 'SCRIPT_FILENAME' => '');
	chdir(str_replace('\\', '/', dirname(__FILE__)) . '/../../../');
	
	require 'inc/functions.php';
	
	require dirname(__FILE__) . '/theme.php';
	rebuildTheme('rrdtool', 'cron');

