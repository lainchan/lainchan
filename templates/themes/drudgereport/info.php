<?php
	$theme = Array();
	
	// Theme name
	$theme['name'] = 'Drudge Report';
	// Description (you can use Tinyboard markup here)
	$theme['description'] = 'Recent posts theme, styled like Druge Report.';
	$theme['version'] = 'v0.1';
	
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
	$theme['build_function'] = 'drudge_build';
?>
