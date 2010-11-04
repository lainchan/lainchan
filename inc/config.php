<?php
	
	// Database stuff
	define('MY_SERVER',		'localhost');
	define('MY_USER',		'');
	define('MY_PASSWORD',	'');
	define('MY_DATABASE',	'');
	
	// The name of the session cookie (PHP's $_SESSION)
	define('SESS_COOKIE',	'imgboard');
	
	// Used to safely determine when the user was first seen, to prevent floods.
	// time()
	define('TIME_COOKIE',	'arrived');
	// HASH_COOKIE contains an MD5 hash of TIME_COOKIE+SALT for verification.
	define('HASH_COOKIE',	'hash');
	
	// How long should the cookies last (in seconds)
	define('COOKIE_EXPIRE',	15778463); //6 months
	
	define('SALT',			'wefaw98YHEWUFuo');
	
	// How many seconds before you can post, after the first visit
	define('LURKTIME',		30);
	
	// Max body length
	define('MAX_BODY',		1800);
	
	define('THREADS_PER_PAGE',	10);
	define('MAX_PAGES',			5);
	define('THREADS_PREVIEW',	5);
	
	// Error messages
	define('ERROR_LURK',	'Lurk some more before posting.');
	define('ERROR_BOT',		'You look like a bot.');
	define('ERROR_TOOLONG',	'The %s field was too long.');
	define('ERROR_TOOLONGBODY', 'The body was too long.');
	define('ERROR_TOOSHORTBODY', 'The body was too short or empty.');
	define('ERROR_NOIMAGE',	'You must upload an image.');
	define('ERROR_NOMOVE',	'The server failed to handle your upload.');
	define('ERROR_FILEEXT',	'Unsupported image format.');
	define('ERR_INVALIDIMG','Invalid image.');
	define('ERR_FILESIZE', 'Maximum file size: %maxsz%<br>Your file\'s size: %sz%');
	define('ERR_MAXSIZE', 'The file was too big.');
	
	// For resizing, max values
	define('THUMB_WIDTH',	200);
	define('THUMB_HEIGHT',	200);
	
	// Maximum image upload size in bytes
	define('MAX_FILESIZE',	6930209); // 10MB
	// Maximum image dimensions
	define('MAX_WIDTH',		10000);
	define('MAX_HEIGHT',	MAX_WIDTH);
	
	define('ALLOW_ZIP',		true);
	define('ZIP_IMAGE',		'src/zip.png');
	
	
	/**
		Redraw the image using GD functions to strip any excess data (commonly ZIP archives)
		WARNING: Very beta. Currently strips animated GIFs too :(
	**/
	define('REDRAW_IMAGE', true);
	// Redrawing configuration
	define('JPEG_QUALITY',	100);
	define('REDRAW_GIF',	false);
	
	// Display the aspect ratio in a post's file info
	define('SHOW_RATIO',	true);
	
	define('DIR_IMG',		'src/');
	define('DIR_THUMB',		'thumb/');
	define('DIR_RES',		'res/');
	
	// The root directory, including the trailing slash, for Tinyboard.
	// examples: '/', '/board/', '/chan/'
	define('ROOT',			'/');
	define('POST_URL',		ROOT . 'post.php');
	define('FILE_INDEX',	'index.html');
	define('FILE_PAGE',		'%d.html');
	
	// Automatically convert things like "..." to Unicode characters ("�")
	define('AUTO_UNICODE',	true);
	// Whether to turn URLs into functional links
	define('MARKUP_URLS',	true);
	define('URL_REGEX',		'/' .	'(https?|ftp):\/\/' .	'([\w\-]+\.)+[a-zA-Z]{2,6}' .	'(\/([\w\-~\.#\/?=&;:+%]+))?' . '/');
	
	// Allowed file extensions
	$allowed_ext = Array('jpg', 'jpeg', 'bmp', 'gif', 'png');
	
	define('BUTTON_NEWTOPIC',	'New Topic');
	define('BUTTON_REPLY',		'New Reply');
	
	define('ALWAYS_NOKO',		false);
	
	define('URL_MATCH',		'/^' . (@$_SERVER['HTTPS']?'https':'http').':\/\/'.$_SERVER['HTTP_HOST'] . '(' . preg_quote(ROOT, '/') . '|' . preg_quote(ROOT, '/') . '' . preg_quote(FILE_INDEX, '/') . '|' . preg_quote(ROOT, '/') . '' . str_replace('%d', '\d+', preg_quote(FILE_PAGE, '/')) . ')$/');
	
	if(!defined('IS_INSTALLATION')) {
		if(!file_exists(DIR_IMG)) @mkdir(DIR_IMG) or error("Couldn't create " . DIR_IMG . ". Install manually.");
		if(!file_exists(DIR_THUMB)) @mkdir(DIR_THUMB) or error("Couldn't create " . DIR_IMG . ". Install manually.");
		if(!file_exists(DIR_RES)) @mkdir(DIR_RES) or error("Couldn't create " . DIR_IMG . ". Install manually.");
	}
?>