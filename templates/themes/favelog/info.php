<?php
	$theme = array();
	
	// Theme name
	$theme['name'] = 'Favelog';
	// Description (you can use Tinyboard markup here)
	$theme['description'] = 'Show a post favelog.';
	$theme['version'] = 'v0.1';
	
	// Theme configuration	
	$theme['config'] = Array();
	
	$theme['config'][] = Array(
		'title' => 'Title',
		'name' => 'title',
		'type' => 'text',
		'default' => 'Favelog'
	);
	
	$__boards = listBoards();
	$__default_boards = Array();
	foreach ($__boards as $__board)
		$__default_boards[] = $__board['uri'];
	
	$theme['config'][] = Array(
		'title' => 'Included boards',
		'name' => 'boards',
		'type' => 'text',
		'comment' => '(space seperated)',
		'default' => implode(' ', $__default_boards)
	);
	
	$theme['config'][] = Array(
		'title' => 'CSS file',
		'name' => 'css',
		'type' => 'text',
		'default' => 'catalog.css',
		'comment' => '(eg. "catalog.css")'
	);
	
	$theme['config'][] = Array(
		'title' => 'Update on new posts',
		'name' => 'update_on_posts',
		'type' => 'checkbox',
		'default' => true,
		'comment' => 'Without this checked, the catalog only updates on new threads.'
	);

	$theme['config'][] = Array(
		'title' => 'Use tooltipster',
		'name' => 'use_tooltipster',
		'type' => 'checkbox',
		'default' => true,
		'comment' => 'Check this if you wish to show a nice tooltip with info about the thread on mouse over. Texts only available in PT-br.'
	);

	// Unique function name for building everything
	$theme['build_function'] = 'favelog_build';
