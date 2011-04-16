<?php
	$theme = Array();
	
	// Theme name
	$theme['name'] = 'RecentPosts';
	// Description (you can use Tinyboard markup here)
	$theme['description'] = 'Show recent posts and images, like 4chan.';
	$theme['version'] = 'v0.9';
	
	// Theme configuration	
	$theme['config'] = Array();
	
	$theme['config'][] = Array(
		'title' => 'Title',
		'name' => 'title',
		'type' => 'text'
	);
	
	$theme['config'][] = Array(
		'title' => 'Excluded boards',
		'name' => 'exclude',
		'type' => 'text',
		'comment' => '(space seperated)'
	);
	
	// Unique function name for building everything
	$theme['build_function'] = 'recentposts_build';
?>