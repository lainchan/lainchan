<?php
	$theme = [];
	
	// Theme name
	$theme['name'] = 'Overboard (Ukko2)';
	// Description (you can use Tinyboard markup here)
	$theme['description'] = 'Board with threads and messages from all boards';
	$theme['version'] = 'v0.2';
	
	// Theme configuration	
	$theme['config'] = [];
	
	$theme['config'][] = ['title' => 'Board name', 'name' => 'title', 'type' => 'text', 'default' => 'Ukko'];
	$theme['config'][] = ['title' => 'Board URI', 'name' => 'uri', 'type' => 'text', 'default' => '*', 'comment' => '(ukko for example)'];	
	$theme['config'][] = ['title' => 'Subtitle', 'name' => 'subtitle', 'type' => 'text', 'comment' => '(%s = thread limit. for example "%s freshly bumped threads")'];		
	$theme['config'][] = ['title' => 'included boards', 'name' => 'include', 'type' => 'text', 'comment' => '(space seperated)'];
	$theme['config'][] = ['title' => 'Number of threads', 'name' => 'thread_limit', 'type' => 'text', 'default' => '15'];	
	// Unique function name for building everything
	$theme['build_function'] = 'ukko2_build';
	$theme['install_callback'] = 'ukko2_install';

	if(!function_exists('ukko2_install')) {
		function ukko2_install($settings) {
			if (!file_exists($settings['uri']))
				@mkdir($settings['uri'], 0777) or error("Couldn't create " . $settings['uri'] . ". Check permissions.", true);
	                file_write($settings['uri'] . '/ukko.js', Element('themes/ukko/ukko.js', []));
		}
	}
	
