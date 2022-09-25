<?php
	$theme = [];
	
	// Theme name
	$theme['name'] = \Calendar::class;
	// Description (you can use Tinyboard markup here)
	$theme['description'] = 'Calendar theme.';
	$theme['version'] = 'v0.0.1';
	
	// Theme configuration	
	$theme['config'] = [];
	
	$theme['config'][] = ['title' => 'Site title', 'name' => 'title', 'type' => 'text'];
	
	$theme['config'][] = ['title' => 'Slogan', 'name' => 'subtitle', 'type' => 'text', 'comment' => '(optional)'];
	
	$theme['config'][] = ['title' => 'File', 'name' => 'file', 'type' => 'text', 'default' => $config['file_index'], 'comment' => '(eg. "index.html")'];
	
	// Unique function name for building everything
	$theme['build_function'] = 'calendar_build';

