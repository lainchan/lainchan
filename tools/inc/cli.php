<?php

/*
 *  This script will look for Tinyboard in the following places (in order):
 *    - $TINYBOARD_PATH environment varaible
 *    - ./
 *    - ./Tinyboard/
 *    - ../
 */

$shell_path = getcwd();

if(getenv('TINYBOARD_PATH') !== false)
	$dir = getenv('TINYBOARD_PATH');
elseif(file_exists('inc/functions.php'))
	$dir = false;
elseif(file_exists('Tinyboard') && is_dir('Tinyboard') && file_exists('Tinyboard/inc/functions.php'))
	$dir = 'Tinyboard';
elseif(file_exists('../inc/functions.php'))
	$dir = '..';
else
	die('Could not locate Tinyboard directory!');

if($dir && !chdir($dir))
	die('Could not change directory to ' . $dir . '!');

if(!getenv('TINYBOARD_PATH')) {
	// follow symlink
	chdir(realpath('inc') . '/..');
}

echo 'Tinyboard: ' . getcwd() . "\n";

require 'inc/functions.php';
require 'inc/display.php';
require 'inc/template.php';
require 'inc/database.php';
require 'inc/user.php';
require 'inc/mod.php';

$mod = Array(
	'id' => -1,
	'type' => ADMIN,
	'username' => '?',
	'boards' => Array('*')
);

function get_httpd_privileges() {
	global $config, $shell_path;
	
	if(php_sapi_name() != 'cli')
		die("get_httpd_privileges(): invoked from HTTP client.\n");
	
	echo "Dropping priviledges...\n";
	
	if(!is_writable('.'))
		die("get_httpd_privileges(): web directory is not writable\n");
	
	if(!is_writable('inc/'))
		die("get_httpd_privileges(): inc/ directory is not writable\n");
	
	$filename = '.' . md5(rand()) . '.php';
	
	echo "Copying rebuilder to web directory...\n";
	
	copy($shell_path . '/' . $_SERVER['PHP_SELF'], $filename);
	copy(__FILE__, 'inc/cli.php');
	
	chmod($filename, 0666);
	chmod('inc/cli.php', 0666);
	
	if(preg_match('/^https?:\/\//', $config['root'])) {
		$url = $config['root'] . $filename;
	} else {
		// assume localhost
		$url = 'http://localhost' . $config['root'] . $filename;
	}
	
	echo "Downloading $url\n";
	
	passthru('curl -s -N ' . escapeshellarg($url));
	
	unlink($filename);
	unlink('inc/cli.php');
	
	exit(0);
}

