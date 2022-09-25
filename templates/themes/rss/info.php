<?php
	$theme = [];
	
	// Theme name
	$theme['name'] = 'RSS';
	// Description (you can use Tinyboard markup here)
	$theme['description'] = 'Show recent posts and images as a RSS';
	$theme['version'] = 'v0.1';
	
	// Theme configuration	
	$theme['config'] = [];
	
	$theme['config'][] = ['title' => 'Title', 'name' => 'title', 'type' => 'text', 'default' => 'Recent Posts RSS'];
	
	$theme['config'][] = ['title' => 'Excluded boards', 'name' => 'exclude', 'type' => 'text', 'comment' => '(space seperated)'];
	
	$theme['config'][] = ['title' => '# of recent posts', 'name' => 'limit_posts', 'type' => 'text', 'default' => '30', 'comment' => '(maximum posts to display)'];
	
	$theme['config'][] = ['title' => 'XML file', 'name' => 'xml', 'type' => 'text', 'default' => 'recent.xml', 'comment' => '(eg. "recent.xml")'];
	
	$theme['config'][] = ['title' => 'Base URL', 'name' => 'base_url', 'type' => 'text', 'default' => 'http://test.com', 'comment' => '(eg. "http://test.com")'];
	
	$theme['config'][] = ['title' => 'Show Threads Only', 'name' => 'show_threads_only', 'type' => 'text', 'default' => 'false'];
	
	$theme['config'][] = ['title' => 'Enable Per Board', 'name' => 'enable_per_board', 'type' => 'text', 'default' => 'false'];

	// Unique function name for building everything
	$theme['build_function'] = 'rss_recentposts_build';
	$theme['install_callback'] = 'rss_recentposts_install';

	if (!function_exists('rss_recentposts_install')) {
		function rss_recentposts_install($settings) {
			if (!is_numeric($settings['limit_posts']) || $settings['limit_posts'] < 0)
				return [false, '<strong>' . utf8tohtml($settings['limit_posts']) . '</strong> is not a non-negative integer.'];
		}
	}
	
