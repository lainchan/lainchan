<?php

	// Basic theme properties
	$theme = [
     'name'        => 'Mixed All/Random Overboard',
     // Description (you can use Tinyboard markup here)
     'description' => 'Board with threads from all boards with most recently bumped and random ones intermixed',
     'version'     => 'v0.1',
     // Unique function name for building and installing whatever's necessary
     'build_function'   => 'semirand_build',
     'install_callback' => 'semirand_install',
 ];

	// Theme configuration
	$theme['config'] = [['title'   => 'Board name', 'name'    => 'title', 'type'    => 'text', 'default' => 'Semirandom'], ['title'   => 'Board URI', 'name'    => 'uri', 'type'    => 'text', 'default' => '.', 'comment' => '("mixed", for example)'], ['title'   => 'Subtitle', 'name'    => 'subtitle', 'type'    => 'text', 'comment' => '(%s = thread limit, for example "%s coolest threads")'], ['title'   => 'Excluded boards', 'name'    => 'exclude', 'type'    => 'text', 'comment' => '(space seperated)'], ['title'   => 'Number of threads', 'name'    => 'thread_limit', 'type'    => 'text', 'default' => '15'], ['title'   => 'Random threads', 'name'    => 'random_count', 'comment' => '(number of consecutive random threads)', 'type'    => 'text', 'default' => '1'], ['title'   => 'Bumped threads', 'name'    => 'recent_count', 'comment' => '(number of consecutive recent threads)', 'type'    => 'text', 'default' => '1']];

	if (!function_exists('semirand_install')) {
		function semirand_install($settings) {
			if (!file_exists($settings['uri'])) {
				@mkdir($settings['uri'], 0777) or error("Couldn't create {$settings['uri']}. Check permissions.", true);
			}
		}
	}

