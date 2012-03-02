#!/usr/bin/php
<?php
	error_reporting(0);
	require 'inc/functions.php';
	require 'inc/display.php';
	require 'inc/template.php';
	require 'inc/database.php';
	require 'inc/user.php';
	require 'inc/mod.php';
	error_reporting(E_ALL ^ E_DEPRECATED);
				
	set_time_limit($config['mod']['rebuild_timelimit']);
		
	echo '== Tinyboard '.$config['version'].' =='."\n";	
	$start = microtime(true);
	echo 'Rebuilding...'."\n";

	if (!is_writable ("main.js")) {
		echo 'Dropping priviledges... (I can\'t operate as user, I need httpd rights)'."\n";
		$filename = ".".rand().".".rand().".php";
		echo 'Copying rebuilder...'."\n";
		copy($_SERVER['PHP_SELF'], $filename);
		chmod($filename, 0666);
		echo 'Connecting...!'."\n\n";
		
		// REPLACE http://0/Tinyboard/ WITH YOUR OWN PATH
		passthru("curl -s -N http://0/Tinyboard/$filename");

		echo "\n".'Cleaning up afterwards...'."\n";
		unlink($filename);
		echo "Bye!\n";
		exit;
	}
			
	echo 'Clearing template cache...'."\n";
	$twig = new Twig_Environment($loader, Array(
		'cache' => "{$config['dir']['template']}/cache"
	));
	$twig->clearCacheFiles();
		
	echo 'Regenerating theme files...'."\n";
	rebuildThemes('all');
			
	echo 'Generating Javascript file...'."\n";
	buildJavascript();
			
	$boards = listBoards();
			
	foreach($boards as &$board) {
		echo "Opening board /{$board['uri']}/...\n";
		openBoard($board['uri']);
				
		echo 'Creating index pages...'+"\n";
		buildIndex();
				
		$query = query(sprintf("SELECT `id` FROM `posts_%s` WHERE `thread` IS NULL", $board['uri'])) or error(db_error());
		while($post = $query->fetch()) {
			echo "Rebuilding #{$post['id']}...\n";
			buildThread($post['id']);
		}
	}
	echo 'Complete!'."\n";

	printf('Took %g seconds.'."\n", microtime(true) - $start);
			
	//modLog('Rebuilt everything using tools/rebuild.php');
?>
