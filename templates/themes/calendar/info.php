<?php
	$theme = Array();
	
	// Theme name
	$theme['name'] = 'Calendar';
	// Description (you can use Tinyboard markup here)
	$theme['description'] = 'Calendar theme.';
	$theme['version'] = 'v0.0.1';
	
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
		'title' => 'File',
		'name' => 'file',
		'type' => 'text',
		'default' => $config['file_index'],
		'comment' => '(eg. "index.html")'
	);
	
	// Unique function name for building everything
	$theme['build_function'] = 'calendar_build';

