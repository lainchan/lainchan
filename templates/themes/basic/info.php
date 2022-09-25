<?php
	$theme = [];
	
	// Theme name
	$theme['name'] = \Basic::class;
	// Description (you can use Tinyboard markup here)
	$theme['description'] = 'Extremely basic news listing for the homepage. Enabling boardlinks is recommended for this theme.';
	$theme['version'] = 'v0.9.1';
	
	// Theme configuration	
	$theme['config'] = [];
	
	$theme['config'][] = ['title' => 'Site title', 'name' => 'title', 'type' => 'text'];
	
	$theme['config'][] = ['title' => 'Slogan', 'name' => 'subtitle', 'type' => 'text', 'comment' => '(optional)'];
	
	$theme['config'][] = ['title' => 'File', 'name' => 'file', 'type' => 'text', 'default' => $config['file_index'], 'comment' => '(eg. "index.html")'];
	
	$theme['config'][] = ['title' => '# of recent entries', 'name' => 'no_recent', 'type' => 'text', 'default' => 0, 'size' => 3, 'comment' => '(number of recent news entries to display; "0" is infinite)'];
	
	// Unique function name for building everything
	$theme['build_function'] = 'basic_build';
	$theme['install_callback'] = 'build_install';

	if (!function_exists('build_install')) {
		function build_install($settings) {
			if (!is_numeric($settings['no_recent']) || $settings['no_recent'] < 0)
				return [false, '<strong>' . utf8tohtml($settings['no_recent']) . '</strong> is not a non-negative integer.'];
		}
	}

