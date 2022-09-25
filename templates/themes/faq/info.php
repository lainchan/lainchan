<?php
	$theme = [];
	
	// Theme name
	$theme['name'] = \FAQ::class;
	// Description (you can use Tinyboard markup here)
	$theme['description'] = 'FAQ because Lainons have questions';
	$theme['version'] = 'v0.0.1';
	
	// Theme configuration	
	$theme['config'] = [];
	
	$theme['config'][] = ['title' => 'Site title', 'name' => 'title', 'type' => 'text'];
	
	$theme['config'][] = ['title' => 'Slogan', 'name' => 'subtitle', 'type' => 'text', 'comment' => '(optional)'];
	
	$theme['config'][] = ['title' => 'File', 'name' => 'file', 'type' => 'text', 'default' => "faq.html", 'comment' => '(eg. "faq.html")'];
	
	
	// Unique function name for building everything
	$theme['build_function'] = 'faq_build';
