#!/usr/bin/php
<?php
	require dirname(__FILE__) . '/inc/cli.php';
	
	$start = microtime(true);
	
	echo "== Tinyboard {$config['version']} ==\n";	
	
	if(!is_writable($config['file_script'])) {
		get_httpd_privileges();
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
	
	$main_js = $config['file_script'];
	
	$boards = listBoards();
	
	foreach($boards as &$board) {
		echo "Opening board /{$board['uri']}/...\n";
		openBoard($board['uri']);
		
		echo "Creating index pages...\n";
		buildIndex();
		
		if($config['file_script'] != $main_js) {
			// different javascript file
			echo "Generating Javascript file...\n";
			buildJavascript();
		}
		
		$query = query(sprintf("SELECT `id` FROM `posts_%s` WHERE `thread` IS NULL", $board['uri'])) or error(db_error());
		while($post = $query->fetch()) {
			echo "Rebuilding #{$post['id']}...\n";
			buildThread($post['id']);
		}
	}
	
	printf("Complete! Took %g seconds\n", microtime(true) - $start);
	
	modLog('Rebuilt everything using tools/rebuild.php');

