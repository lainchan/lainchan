<?php
	$theme = Array();
	
	// Theme name
	$theme['name'] = 'Categories';
	// Description (you can use Tinyboard markup here)
	$theme['description'] = 
'Group-ordered, category-aware modification of the Frameset theme, with removable sidebar frame.

Requires $config[\'boards\'] and $config[\'categories\'].';
	$theme['version'] = 'v0.2';
	
	// Theme configuration	
	$theme['config'] = Array();
	
	$theme['config'][] = Array(
		'title' => 'Title',
		'name' => 'title',
		'type' => 'text'
	);
	
	$theme['config'][] = Array(
		'title' => 'Slogan',
		'name' => 'subtitle',
		'type' => 'text'
	);
	
	// Unique function name for building everything
	$theme['build_function'] = 'categories_build';
?>
