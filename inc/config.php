<?php

/*
 *  WARNING: This is a project-wide configuration file shared by all Tinyboard users around the globe.
 *  If you would like to make instance-specific changes to your own setup, please use instance-config.php.
 *
 *  This is the default configuration. You can copy values from here and use them in
 * 	your instance-config.php
 *
 */

	// Database stuff
	define('MY_SERVER',		'localhost', true);
	define('MY_USER',		'', true);
	define('MY_PASSWORD',	'', true);
	define('MY_DATABASE',	'', true);

	// The name of the session cookie (PHP's $_SESSION)
	define('SESS_COOKIE',	'imgboard', true);

	// Used to safely determine when the user was first seen, to prevent floods.
	// time()
	define('TIME_COOKIE',	'arrived', true);
	// HASH_COOKIE contains an MD5 hash of TIME_COOKIE+SALT for verification.
	define('HASH_COOKIE',	'hash', true);

	// How long should the cookies last (in seconds)
	define('COOKIE_EXPIRE',	15778463, true); //6 months

	define('SALT',			'wefaw98YHEWUFuo', true);

	// How many seconds before you can post, after the first visit
	define('LURKTIME',		30, true);

	// Max body length
	define('MAX_BODY',		1800, true);

	define('THREADS_PER_PAGE',	10, true);
	define('MAX_PAGES',			5, true);
	define('THREADS_PREVIEW',	5, true);

	// Error messages
	define('ERROR_LURK',	'Lurk some more before posting.', true);
	define('ERROR_BOT',		'You look like a bot.', true);
	define('ERROR_TOOLONG',	'The %s field was too long.', true);
	define('ERROR_TOOLONGBODY', 'The body was too long.', true);
	define('ERROR_TOOSHORTBODY', 'The body was too short or empty.', true);
	define('ERROR_NOIMAGE',	'You must upload an image.', true);
	define('ERROR_NOMOVE',	'The server failed to handle your upload.', true);
	define('ERROR_FILEEXT',	'Unsupported image format.', true);
	define('ERR_INVALIDIMG','Invalid image.', true);
	define('ERR_FILESIZE', 'Maximum file size: %maxsz% bytes<br>Your file\'s size: %filesz% bytes', true);
	define('ERR_MAXSIZE', 'The file was too big.', true);
	define('ERR_INVALIDZIP', 'Invalid archive!', true);

	// For resizing, max values
	define('THUMB_WIDTH',	200, true);
	define('THUMB_HEIGHT',	200, true);

	// Maximum image upload size in bytes
	define('MAX_FILESIZE',	10*1024*1024, true); // 10MB
	// Maximum image dimensions
	define('MAX_WIDTH',		10000, true);
	define('MAX_HEIGHT',	MAX_WIDTH, true);

	/* When you upload a ZIP as a file, all the images inside the archive
	 * get dumped into the thread as replies.
	 * Extremely beta and not recommended yet.
	 */
	define('ALLOW_ZIP',		false, true);
	define('ZIP_IMAGE',		'src/zip.png', true);


	/**
		Redraw the image using GD functions to strip any excess data (commonly ZIP archives)
		WARNING: Very beta. Currently strips animated GIFs too :(
	**/
	define('REDRAW_IMAGE', false, true);
	// Redrawing configuration
	define('JPEG_QUALITY',	100, true);
	define('REDRAW_GIF',	false, true);

	// Display the aspect ratio in a post's file info
	define('SHOW_RATIO',	true, true);

	define('DIR_IMG',		'src/', true);
	define('DIR_THUMB',		'thumb/', true);
	define('DIR_RES',		'res/', true);

	// The root directory, including the trailing slash, for Tinyboard.
	// examples: '/', '/board/', '/chan/'
	define('ROOT',			'/', true);
	
	// If for some reason the folders and static HTML index files aren't in the current working direcotry,
	// enter the directory path here. Otherwise, keep it false.
	define('ROOT_FILE',		false, true);
	
	define('POST_URL',		ROOT . 'post.php', true);
	define('FILE_INDEX',	'index.html', true);
	define('FILE_PAGE',		'%d.html', true);

	// Automatically convert things like "..." to Unicode characters ("�")
	define('AUTO_UNICODE',	true, true);
	// Whether to turn URLs into functional links
	define('MARKUP_URLS',	true, true);
	define('URL_REGEX',		'/' .	'(https?|ftp):\/\/' .	'([\w\-]+\.)+[a-zA-Z]{2,6}' .	'(\/([\w\-~\.#\/?=&;:+%]+))?' . '/', true);

	// Allowed file extensions
	$allowed_ext = Array('jpg', 'jpeg', 'bmp', 'gif', 'png', true);

	define('BUTTON_NEWTOPIC',	'New Topic', true);
	define('BUTTON_REPLY',		'New Reply', true);
	
	define('ALWAYS_NOKO',		false, true);
	
	define('URL_MATCH',		'/^' . (@$_SERVER['HTTPS']?'https':'http').':\/\/'.$_SERVER['HTTP_HOST'] . '(' . preg_quote(ROOT, '/') . '|' . preg_quote(ROOT, '/') . '' . preg_quote(FILE_INDEX, '/') . '|' . preg_quote(ROOT, '/') . '' . str_replace('%d', '\d+', preg_quote(FILE_PAGE, '/')) . ')$/', true);
	
	if(ROOT_FILE) {
		chdir(ROOT_FILE);
	}
	if(!defined('IS_INSTALLATION')) {
		if(!file_exists(DIR_IMG)) @mkdir(DIR_IMG) or error("Couldn't create " . DIR_IMG . ". Install manually.", true);
		if(!file_exists(DIR_THUMB)) @mkdir(DIR_THUMB) or error("Couldn't create " . DIR_IMG . ". Install manually.", true);
		if(!file_exists(DIR_RES)) @mkdir(DIR_RES) or error("Couldn't create " . DIR_IMG . ". Install manually.", true);
	}
?>