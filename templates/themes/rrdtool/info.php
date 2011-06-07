<?php
	$theme = Array();
	
	// Theme name
	$theme['name'] = 'RRDtool';
	// Description (you can use Tinyboard markup here)
	$theme['description'] = 'Graph basic statistics using the PHP RRDtool extension.';
	$theme['version'] = 'v0.1';
	
	// Theme configuration	
	$theme['config'] = Array();
	
	$theme['config'][] = Array(
		'title' => 'Path',
		'name' => 'path',
		'type' => 'text',
		'default' => str_replace('\\', '/', dirname(__FILE__)) . '/data',
		'size' => '50'
	);
	
	$theme['config'][] = Array(
		'title' => 'Images path',
		'name' => 'images',
		'type' => 'text',
		'default' => str_replace('\\', '/', dirname(__FILE__)) . '/images',
		'size' => '50'
	);
	
	$__boards = listBoards();
	$__default_boards = Array();
	foreach($__boards as $__board)
		$__default_boards[] = $__board['uri'];
	
	$theme['config'][] = Array(
		'title' => 'Boards',
		'name' => 'boards',
		'type' => 'text',
		'comment' => '(boards to graph; space seperated)',
		'size' => 24,
		'default' => implode(' ', $__default_boards)
	);
	
	// Unique function name for building everything
	$theme['build_function'] = 'rrdtool_build';
?>