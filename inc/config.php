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
	// Used for moderation login
	define('MOD_COOKIE',	'mod', true);
	// Where to set the 'path' parameter to ROOT when creating cookies. Recommended.
	define('JAIL_COOKIES',	true,	true);
	
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
	
	// For development purposes. Turns 'display_errors' on. Not recommended for production.
	define('VERBOSE_ERRORS',	true, true);

	// Error messages
	define('ERROR_LURK',	'Lurk some more before posting.', true);
	define('ERROR_BOT',		'You look like a bot.', true);
	define('ERROR_TOOLONG',	'The %s field was too long.', true);
	define('ERROR_TOOLONGBODY', 'The body was too long.', true);
	define('ERROR_TOOSHORTBODY', 'The body was too short or empty.', true);
	define('ERROR_NOIMAGE',	'You must upload an image.', true);
	define('ERROR_NOMOVE',	'The server failed to handle your upload.', true);
	define('ERROR_FILEEXT',	'Unsupported image format.', true);
	define('ERROR_NOBOARD',	'Invalid board!', true);
	define('ERROR_NONEXISTANT', 'Thread specified does not exist.', true);
	define('ERROR_NOPOST',	'You didn\'t make a post.', true);
	define('ERR_INVALIDIMG','Invalid image.', true);
	define('ERR_FILESIZE',	'Maximum file size: %maxsz% bytes<br>Your file\'s size: %filesz% bytes', true);
	define('ERR_MAXSIZE',	'The file was too big.', true);
	define('ERR_INVALIDZIP','Invalid archive!', true);
	
	// Moderator errors
	define('ERROR_INVALID',	'Invalid username and/or password.', true);
	define('ERROR_INVALIDAFTER', 'Invalid username and/or password. Your user may have been deleted or changed.');
	define('ERROR_MALFORMED','Invalid/malformed cookies.', true);
	define('ERROR_MISSEDAFIELD', 'Your browser didn\'t submit an input when it should have.', true);
	define('ERROR_REQUIRED', 'The %s field is required.', true);
	define('ERROR_INVALIDFIELD', 'The %s field was invalid.', true);
	
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
	
	// Where to store the .html templates. This folder and templates must exist or fatal errors will be thrown.
	define('DIR_TEMPLATE',	getcwd() . '/templates', true);
	
	// The root directory, including the trailing slash, for Tinyboard.
	// examples: '/', 'http://boards.chan.org/', '/chan/'
	define('ROOT',			'/', true);
	
	// If for some reason the folders and static HTML index files aren't in the current working direcotry,
	// enter the directory path here. Otherwise, keep it false.
	define('ROOT_FILE',		false, true);
	
	define('POST_URL',		ROOT . 'post.php', true);
	define('FILE_INDEX',	'index.html', true);
	define('FILE_PAGE',		'%d.html', true);
	
	// Multi-board (%s is board abbreviation)
	define('BOARD_PATH', '%s/', true);
	
	// The HTTP status code to use when redirecting.
	// Should be 3xx (redirection). http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
	// "302" is recommended.
	define('REDIRECT_HTTP',	302, true);
	
	/* 
		Mod stuff
	*/
	// Whether or not to lock moderator sessions to the IP address that was logged in with.
	define('MOD_LOCK_IP',	true,	true);
	// The page that is first shown when a moderator logs in. Defaults to the dashboard.
	define('MOD_DEFAULT',	'/',	true);
	
	define('MOD_JANITOR',	0,		true);
	define('MOD_MOD',		1,		true);
	define('MOD_ADMIN',		2,		true);
	
	// A small file in the main directory indicating that the script has been ran and the board(s) have been generated.
	// This keeps the script from querying the database and causing strain when not needed.
	define('HAS_INSTALLED',		'.installed', true);
	
	// Name of the boards. Typically '/%s/' (/b/, /mu/, etc)
	// BOARD_ABBREVIATION - BOARD_TITLE
	define('BOARD_ABBREVIATION', '/%s/', true);
	
	// Automatically convert things like "..." to Unicode characters ("…")
	define('AUTO_UNICODE',	true, true);
	// Use some Wiki-like syntax (''em'', '''strong''', ==Heading==, etc)
	define('WIKI_MARKUP',	true, true);
	// Whether to turn URLs into functional links
	define('MARKUP_URLS',	true, true);
	// Complex regular expression to catch URLs
	define('URL_REGEX',		'/' .	'(https?|ftp):\/\/' .	'(([\w\-]+\.)+[a-zA-Z]{2,6}|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})' .	'(\/([\w\-~\.#\/?=&;:+%]+))?' . '/', true);
	
	// Allowed file extensions
	$allowed_ext = Array('jpg', 'jpeg', 'bmp', 'gif', 'png', true);

	define('BUTTON_NEWTOPIC',	'New Topic', true);
	define('BUTTON_REPLY',		'New Reply', true);
	
	define('ALWAYS_NOKO',		false, true);
	
	define('URL_MATCH',		'/^' .
			(@$_SERVER['HTTPS']?'https':'http').':\/\/'.$_SERVER['HTTP_HOST'] .
			'(' .
					preg_quote(ROOT, '/') .
					str_replace('%s', '\w{1,8}', preg_quote(BOARD_PATH, '/')) .
				'|' .
					preg_quote(ROOT, '/') .
					str_replace('%s', '\w{1,8}', preg_quote(BOARD_PATH, '/')) .
					preg_quote(FILE_INDEX, '/') .
				'|' .
					preg_quote(ROOT, '/') .
					str_replace('%s', '\w{1,8}', preg_quote(BOARD_PATH, '/')) .
					str_replace('%d', '\d+', preg_quote(FILE_PAGE, '/')) .
			')$/', true);
	
	if(ROOT_FILE) {
		chdir(ROOT_FILE);
	}
	
	if(VERBOSE_ERRORS) {
		error_reporting(E_ALL);
		ini_set('display_errors', 1);
	}
	
	/*
	Multi-board support removes any use for this.
	
	if(!defined('IS_INSTALLATION')) {
		if(!file_exists(DIR_IMG)) @mkdir(DIR_IMG, 0777) or error("Couldn't create " . DIR_IMG . ". Install manually.", true);
		if(!file_exists(DIR_THUMB)) @mkdir(DIR_THUMB, 0777) or error("Couldn't create " . DIR_IMG . ". Install manually.", true);
		if(!file_exists(DIR_RES)) @mkdir(DIR_RES, 0777) or error("Couldn't create " . DIR_IMG . ". Install manually.", true);
	}
	*/
?>