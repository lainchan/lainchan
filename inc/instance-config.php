<?php

/*
 *  Instance Configuration
 *  ----------------------
 *  Edit this file and not config.php for imageboard configuration.
 *
 *  You can copy values from config.php (defaults) and paste them here.
 */



	// Database stuff
	$config['db']['type']		= 'mysql';
	$config['db']['server']		= 'localhost';
	$config['db']['user']		= '';
	$config['db']['password']	= '';
	$config['db']['database']	= '';
	
	$config['root']				= '/';
	
	
	
	// The following looks ugly. I will find a better place to put this code soon.
	$config['post_url']		= $config['root'] . 'post.php';
	
	$config['url_match'] = '/^' .
		(preg_match($config['url_regex'], $config['root']) ? '' :
			(@$_SERVER['HTTPS']?'https':'http') .
			':\/\/'.$_SERVER['HTTP_HOST']) .
			preg_quote($config['root'], '/') .
		'(' .
				str_replace('%s', '\w{1,8}', preg_quote($config['board_path'], '/')) .
			'|' .
				str_replace('%s', '\w{1,8}', preg_quote($config['board_path'], '/')) .
				preg_quote($config['file_index'], '/') .
			'|' .
				str_replace('%s', '\w{1,8}', preg_quote($config['board_path'], '/')) .
				str_replace('%d', '\d+', preg_quote($config['file_page'], '/')) .
			'|' .
				preg_quote($config['file_mod'], '/') .
			'\?\/.+' .
		')$/i';
	
	$config['dir']['static']	= $config['root'] . 'static/';
	
	$config['image_sticky']		= $config['dir']['static'] . 'sticky.gif';
	$config['image_locked']		= $config['dir']['static'] . 'locked.gif';
	$config['image_deleted']	= $config['dir']['static'] . 'deleted.png';
	$config['image_zip']		= $config['dir']['static'] . 'zip.png';
?>