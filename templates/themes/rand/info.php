<?php
	$theme = array();
	
	// Theme name
	$theme['name'] = 'Random Overboard';
	// Description (you can use Tinyboard markup here)
	$theme['description'] = 'Board with threads and messages from all boards, sorted randomly';
	$theme['version'] = 'v0.2';
	
	// Theme configuration	
	$theme['config'] = array();
	
	$theme['config'][] = array(
		'title' => 'Board name',
		'name' => 'title',
		'type' => 'text',
		'default' => 'Random'
	);
	$theme['config'][] = array(
		'title' => 'Board URI',
		'name' => 'uri',
		'type' => 'text',
		'default' => '.',
		'comment' => '(rand for example)'
	);	
	$theme['config'][] = array(
		'title' => 'Subtitle',
		'name' => 'subtitle',
		'type' => 'text',
		'comment' => '(%s = thread limit. for example "%s freshly bumped threads")'
	);		
	$theme['config'][] = array(
		'title' => 'Excluded boards',
		'name' => 'exclude',
		'type' => 'text',
		'comment' => '(space seperated)'
	);
	$theme['config'][] = array(
		'title' => 'Number of threads',
		'name' => 'thread_limit',
		'type' => 'text',
		'default' => '15',
	);	
	// Unique function name for building everything
	$theme['build_function'] = 'rand_build';
	$theme['install_callback'] = 'rand_install';

	if(!function_exists('rand_install')) {
		function rand_install($settings) {
			if (!file_exists($settings['uri']))
				@mkdir($settings['uri'], 0777) or error("Couldn't create " . $settings['uri'] . ". Check permissions.", true);
		}
	}
	
