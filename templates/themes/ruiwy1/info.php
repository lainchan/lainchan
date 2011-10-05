<?php
	$theme = Array();
	
	// Theme name
	$theme['name'] = 'Rui\'s Mod';
	// Description (you can use Tinyboard markup here)
	$theme['description'] = 'Extremely basic news listing for the homepage. It\'s suggested that you enable boardlinks for this theme.';
	$theme['version'] = 'v0.9';
	
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
	$theme['build_function'] = 'rui_build';
?>
