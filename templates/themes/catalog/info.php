<?php
	$theme = [];

	// Theme name
	$theme['name'] = \Catalog::class;
	// Description (you can use Tinyboard markup here)
	$theme['description'] = 'Show a post catalog.';
	$theme['version'] = 'v0.2.1';

	// Theme configuration
	$theme['config'] = [];

	$theme['config'][] = ['title' => 'Title', 'name' => 'title', 'type' => 'text', 'default' => \Catalog::class];

	$__boards = listBoards();
	$__default_boards = [];
	foreach ($__boards as $__board)
		$__default_boards[] = $__board['uri'];

	$theme['config'][] = ['title' => 'Included boards', 'name' => 'boards', 'type' => 'text', 'comment' => '(space seperated)', 'default' => implode(' ', $__default_boards)];

	$theme['config'][] = ['title' => 'Update on new posts', 'name' => 'update_on_posts', 'type' => 'checkbox', 'default' => false, 'comment' => 'Without this checked, the catalog only updates on new threads.'];

	$theme['config'][] = ['title' => 'Enable Ukko catalog', 'name' => 'enable_ukko', 'type' => 'checkbox', 'default' => false, 'comment' => 'Enable catalog for the Ukko theme. This requires the Ukko theme to be enabled.'];

	$theme['config'][] = ['title' => 'Enable Ukko2 catalog', 'name' => 'enable_ukko2', 'type' => 'checkbox', 'default' => false, 'comment' => 'Enable catalog for the Ukko2 theme. This requires the Ukko2 theme to be enabled.'];
	
	$theme['config'][] = ['title' => 'Enable Ukko3 catalog', 'name' => 'enable_ukko3', 'type' => 'checkbox', 'default' => false, 'comment' => 'Enable catalog for the Ukko theme. This requires the Ukko3 theme to be enabled.'];
	$theme['config'][] = ['title' => 'Enable Ukko4 catalog', 'name' => 'enable_ukko4', 'type' => 'checkbox', 'default' => false, 'comment' => 'Enable catalog for the Ukko theme. This requires the Ukko4 theme to be enabled.'];

	$theme['config'][] = ['title' => 'Enable Rand catalog', 'name' => 'enable_rand', 'type' => 'checkbox', 'default' => false, 'comment' => 'Enable catalog for the Rand theme. This requires the Rand theme to be enabled.'];
	$theme['config'][] = ['title' => 'Use tooltipster', 'name' => 'use_tooltipster', 'type' => 'checkbox', 'default' => true, 'comment' => 'Check this if you wish to show a nice tooltip with info about the thread on mouse over.'];

	$theme['config'][] = ['title' => 'Base URL', 'name' => 'base_url', 'type' => 'text', 'default' => 'http://test.com', 'comment' => '(eg. "http://test.com")'];

	// Unique function name for building everything
	$theme['build_function'] = 'catalog_build';

