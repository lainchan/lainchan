<?php
	$theme = Array();
	
	// Theme name
	$theme['name'] = 'Favelaframes';
	// Description (you can use Tinyboard markup here)
	$theme['description'] = 
'Use a basic frameset layout, with a list of boards and pages on a sidebar to the left of the page.

Users never have to leave the homepage; they can do all their browsing from the one page.';
	$theme['version'] = 'v0.1';
	
	// Theme configuration	
	$theme['config'] = Array();
	
	$theme['config'][] = Array(
		'title' => 'Site title',
		'name' => 'title',
		'type' => 'text'
	);
	
	$theme['config'][] = Array(
		'title' => 'Slogan',
		'name' => 'subtitle',
		'type' => 'text',
		'comment' => '(optional)'
	);
	
	$theme['config'][] = Array(
		'title' => 'Main HTML file',
		'name' => 'file_main',
		'type' => 'text',
		'default' => $config['file_index'],
		'comment' => '(eg. "index.html")'
	);
	
	$theme['config'][] = Array(
		'title' => 'Sidebar file',
		'name' => 'file_sidebar',
		'type' => 'text',
		'default' => 'sidebar.html',
		'comment' => '(eg. "sidebar.html")'
	);
	
	$theme['config'][] = Array(
		'title' => 'News file',
		'name' => 'file_news',
		'type' => 'text',
		'default' => 'news.html',
		'comment' => '(eg. "news.html")'
	);

	$theme['config'][] = Array(
		'title' => 'IRC url',
		'name' => 'irc_url',
		'type' => 'text',
		'default' => 'http://qchat.rizon.net/?nick=Anao.&channels=55ch&uio=d4',
		'comment' => '(optional) Link to IRC channel'
	);

	$theme['config'][] = Array(
		'title' => 'IRC address',
		'name' => 'irc_address',
		'type' => 'text',
		'default' => '#55ch@rizon.net',
		'comment' => '(optional) IRC channel address to appear as a label to the url entered above'
	);
	
	$theme['config'][] = Array(
		'title' => 'CSS file',
		'name' => 'css',
		'type' => 'text',
		'default' => 'favelaframes.css',
		'comment' => '(eg. "favelaframes.css")'
	);

	$theme['config'][] = Array(
		'title' => 'Try the little responsive thingy thing',
		'name' => 'tryresponsive',
		'type' => 'checkbox',
		'default' => true,
		'comment' => 'With this checked, the menu frame will become collapsible when viewport width is under 767px.'
	);

	// Unique function name for building everything
	$theme['build_function'] = 'favelaframes_build';
?>
