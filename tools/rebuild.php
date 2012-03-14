#!/usr/bin/php
<?php
	require 'inc/functions.php';
	require 'inc/display.php';
	require 'inc/template.php';
	require 'inc/database.php';
	require 'inc/user.php';
	require 'inc/mod.php';
	
	require dirname(__FILE__) . '/inc/cli.php';
	
	$mod = Array(
		'id' => -1,
		'type' => ADMIN,
		'username' => '?',
		'boards' => Array('*')
	);
	
	$start = microtime(true);
	
	echo "== Tinyboard {$config['version']} ==\n";	
	
	if(!is_writable($config['file_script'])) {
		echo "Dropping priviledges... (I can't operate as user; I need PHP's rights.)\n";
		
		$filename = '.' . md5(rand()) . '.php';
		
		echo "Copying rebuilder to web directory...\n";
		copy(__FILE__, $filename);
		chmod($filename, 0666);
		
		if(preg_match('/^https?:\/\//', $config['root'])) {
			$url = $config['root'] . $filename;
		} else {
			// assume localhost
			$url = 'http://localhost' . $config['root'] . $filename;
		}
		
		echo "Downloading $url\n";
		
		passthru('curl -s -N ' . escapeshellarg($url));

		echo "\n".'Cleaning up afterwards...'."\n";
		
		unlink($filename);
		
		echo "Bye!\n";
		exit;
	}
	
	echo "Clearing template cache...\n";
	$twig = new Twig_Environment($loader, Array(
		'cache' => "{$config['dir']['template']}/cache"
	));
	$twig->clearCacheFiles();
	
	echo "Regenerating theme files...\n";
	rebuildThemes('all');
	
	echo "Generating Javascript file...\n";
	buildJavascript();
	
	$boards = listBoards();
	
	foreach($boards as &$board) {
		echo "Opening board /{$board['uri']}/...\n";
		openBoard($board['uri']);
		
		echo "Creating index pages...\n";
		buildIndex();
		
		$query = query(sprintf("SELECT `id` FROM `posts_%s` WHERE `thread` IS NULL", $board['uri'])) or error(db_error());
		while($post = $query->fetch()) {
			echo "Rebuilding #{$post['id']}...\n";
			buildThread($post['id']);
		}
	}
	
	printf("Complete! Took %g seconds\n", microtime(true) - $start);
	
	modLog('Rebuilt everything using tools/rebuild.php');

