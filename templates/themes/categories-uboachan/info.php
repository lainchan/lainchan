<?php
	$theme = [];
	
	// Theme name
	$theme['name'] = 'Categories-Uboachan';
	// Description (you can use Tinyboard markup here)
	$theme['description'] = 
'Uboachan modification of the Categories theme.

Requires $config[\'boards\'] and $config[\'categories\'].';
	$theme['version'] = 'v0.2.1';
	
	// Theme configuration	
	$theme['config'] = [];
	
	$theme['config'][] = ['title' => 'Title', 'name' => 'title', 'type' => 'text'];
	
	$theme['config'][] = ['title' => 'Slogan', 'name' => 'subtitle', 'type' => 'text'];
	
	// Unique function name for building everything
	$theme['build_function'] = 'categories_build';
	
	$theme['install_callback'] = 'categories_install';
	if(!function_exists('categories_install')) {
		function categories_install($settings) {
			global $config;
			
			if(!isset($config['boards']) || !isset($config['categories'])) {
				return [false, '<h2>Prerequisites not met!</h2>' . 
					'This theme requires $config[\'boards\'] and $config[\'categories\'] to be set.'];
			}
		}
	}
?>
