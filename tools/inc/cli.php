<?php

/*
 *  This script will look for Tinyboard in the following places (in order):
 *    - $TINYBOARD_PATH environment varaible
 *    - ./
 *    - ./Tinyboard/
 *    - ../
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(0);
$shell_path = getcwd();

if (isset ($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != '127.0.0.1' && $_SERVER['REMOTE_ADDR'] != '::1') {
	die("This script is executable only from Command Line Interface.");
}

if(getenv('TINYBOARD_PATH') !== false)
	$dir = getenv('TINYBOARD_PATH');
elseif(file_exists('inc/functions.php'))
	$dir = false;
elseif(file_exists('Tinyboard') && is_dir('Tinyboard') && file_exists('Tinyboard/inc/functions.php'))
	$dir = 'Tinyboard';
elseif(file_exists('../inc/functions.php'))
	$dir = '..';
else
	die("Could not locate Tinyboard directory!\n");

if($dir && !chdir($dir))
	die("Could not change directory to {$dir}\n");

if(!getenv('TINYBOARD_PATH')) {
	// follow symlink
	chdir(realpath('inc') . '/..');
}

putenv('TINYBOARD_PATH=' . getcwd());

require 'inc/functions.php';
require 'inc/mod/auth.php';

$mod = Array(
	'id' => -1,
	'type' => ADMIN,
	'username' => '?',
	'boards' => Array('*')
);

function get_httpd_privileges() {
	global $config, $shell_path, $argv;
	
	if(php_sapi_name() != 'cli')
		die("get_httpd_privileges(): invoked from HTTP client.\n");
	
	echo "Dropping priviledges...\n";
	
	if(!is_writable('.'))
		die("get_httpd_privileges(): web directory is not writable\n");
	
	$filename = '.' . md5(rand()) . '.php';
	$inc_filename = '.' . md5(rand()) . '.php';
	
	echo "Copying rebuilder to web directory...\n";
	
	// replace "/inc/cli.php" with its new filename
	passthru("cat " . escapeshellarg($shell_path . '/' . $_SERVER['PHP_SELF']) . " | sed \"s/'\/inc\/cli\.php'/'\/{$inc_filename}'/\" > {$filename}");
	
	$inc_header = "<?php\n";
	
	// copy environment
	$env = explode("\n", shell_exec('printenv | grep ^TINYBOARD'));
	foreach($env as $line) {
		if(!empty($line))
			$inc_header .= "putenv('" . addslashes($line) . "');\n";
	}
	
	// copy command line arguments
	$inc_header .= "\$argv = " . var_export($argv, true) . ";\n";
	
	// copy this file
	file_put_contents($inc_filename, $inc_header . substr($inc = file_get_contents(__FILE__), strpos($inc, "\n")));
	
	chmod($filename, 0666);
	chmod($inc_filename, 0666);
	
	if(preg_match('/^https?:\/\//', $config['root'])) {
		$url = $config['root'] . $filename;
	} elseif($host = getenv('TINYBOARD_HOST')) {
		$url = 'http://' . $host . $config['root'] . $filename;
	} else {
		// assume localhost
		$url = 'http://localhost' . $config['root'] . $filename;
	}
	
	echo "Downloading $url\n";
	
	passthru('curl -s -N ' . escapeshellarg($url));
	
	unlink($filename);
	unlink($inc_filename);
	
	exit(0);
}

