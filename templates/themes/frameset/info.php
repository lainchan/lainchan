<?php
	$theme = [];
	
	// Theme name
	$theme['name'] = \Frameset::class;
	// Description (you can use Tinyboard markup here)
	$theme['description'] = 
'Use a basic frameset layout, with a list of boards and pages on a sidebar to the left of the page.

Users never have to leave the homepage; they can do all their browsing from the one page.';
	$theme['version'] = 'v0.1';
	
	// Theme configuration	
	$theme['config'] = [];
	
	$theme['config'][] = ['title' => 'Site title', 'name' => 'title', 'type' => 'text'];
	
	$theme['config'][] = ['title' => 'Slogan', 'name' => 'subtitle', 'type' => 'text', 'comment' => '(optional)'];
	
	$theme['config'][] = ['title' => 'Main HTML file', 'name' => 'file_main', 'type' => 'text', 'default' => $config['file_index'], 'comment' => '(eg. "index.html")'];
	
	$theme['config'][] = ['title' => 'Sidebar file', 'name' => 'file_sidebar', 'type' => 'text', 'default' => 'sidebar.html', 'comment' => '(eg. "sidebar.html")'];
	
	$theme['config'][] = ['title' => 'News file', 'name' => 'file_news', 'type' => 'text', 'default' => 'news.html', 'comment' => '(eg. "news.html")'];
	
	// Unique function name for building everything
	$theme['build_function'] = 'frameset_build';
?>
