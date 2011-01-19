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
	
	// SQL driver ("mysql", "pgsql", "sqlite", "dblib", etc)
	// http://www.php.net/manual/en/pdo.drivers.php
	define('DB_TYPE',		'mysql', true);
	// Hostname or IP address
	define('DB_SERVER',		'localhost', true);
	// Login
	define('DB_USER',		'', true);
	define('DB_PASSWORD',	'', true);
	// TinyBoard database
	define('DB_DATABASE',	'', true);
	// Anything more to add to the DSN string (eg. port=xxx;foo=bar)
	define('DB_DSN', '', true);

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
	// Make this something long and random for security
	define('SALT',			'wefaw98YHEWUFuo', true);
	define('SECURE_TRIP_SALT', '@#$&^@#)$(*&@!_$(&329-8347', true);
	
	// How many seconds before you can post, after the first visit
	define('LURKTIME',		30, true);
	
	// How many seconds between each post
	define('FLOOD_TIME',	4, true);
	// How many seconds between each post with exactly the same content and same IP
	define('FLOOD_TIME_IP_SAME',	120, true);
	// Same as above but different IP address
	define('FLOOD_TIME_SAME',	30, true);
	// Do you need a body for your non-OP posts?
	define('FORCE_BODY',	true, true);

	// Max body length
	define('MAX_BODY',		1800, true);

	define('THREADS_PER_PAGE',	10, true);
	define('MAX_PAGES',			10, true);
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
	define('ERROR_LOCKED',	'Thread locked. You may not reply at this time.', true);
	define('ERROR_NOPOST',	'You didn\'t make a post.', true);
	define('ERROR_FLOOD', 'Flood detected; Post discared.', true);
	define('ERROR_UNORIGINAL', 'Unoriginal content!', true);
	define('ERROR_MUTED', 'Unoriginal content! You have been muted for %d seconds.', true);
	define('ERROR_TOR', 'Hmm… That looks like a Tor exit node.', true);
	define('ERROR_TOOMANYLINKS', 'Too many links; flood detected.', true);
	define('ERR_INVALIDIMG','Invalid image.', true);
	define('ERR_FILESIZE',	'Maximum file size: %maxsz% bytes<br>Your file\'s size: %filesz% bytes', true);
	define('ERR_MAXSIZE',	'The file was too big.', true);
	define('ERR_INVALIDZIP', 'Invalid archive!', true);
	
	// Moderator errors
	define('ERROR_INVALID',	'Invalid username and/or password.', true);
	define('ERROR_NOTAMOD', 'You are not a mod…', true);
	define('ERROR_INVALIDAFTER', 'Invalid username and/or password. Your user may have been deleted or changed.', true);
	define('ERROR_MALFORMED','Invalid/malformed cookies.', true);
	define('ERROR_MISSEDAFIELD', 'Your browser didn\'t submit an input when it should have.', true);
	define('ERROR_REQUIRED', 'The %s field is required.', true);
	define('ERROR_INVALIDFIELD', 'The %s field was invalid.', true);
	define('ERROR_BOARDEXISTS', 'There is already a %s board.', true);
	define('ERROR_NOACCESS', 'You don\'t have permission to do that.', true);
	define('ERROR_INVALIDPOST', 'That post doesn\'t exist…', true);
	define('ERROR_404', 'Page not found.', true);
	
	// For resizing, max values
	define('THUMB_WIDTH',	200, true);
	define('THUMB_HEIGHT',	200, true);
	
	// Store image hash in the database for r9k-like boards implementation soon
	// Function name for hashing
	// sha1_file, md5_file, etc.
	define('FILE_HASH',		'sha1_file', true);
	
	define('BLOCK_TOR',		true, true);
	// Typically spambots try to post a lot of links. Refuse a post with X standalone links?
	define('MAX_LINKS',		7, true);
	
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
	define('DIR_STATIC',	'static/', true);
	
	// Where to store the .html templates. This folder and templates must exist or fatal errors will be thrown.
	define('DIR_TEMPLATE',	getcwd() . '/templates', true);
	
	// The root directory, including the trailing slash, for Tinyboard.
	// examples: '/', 'http://boards.chan.org/', '/chan/'
	define('ROOT',			'/', true);
	
	// Static images
	// These can be URLs OR base64 (data URI scheme)
	define('IMAGE_STICKY',	ROOT . DIR_STATIC . 'sticky.gif', true);
	define('IMAGE_LOCKED',	ROOT . DIR_STATIC . 'locked.gif', true);
	define('DELETED_IMAGE', ROOT . DIR_STATIC . 'deleted.png', true);
	define('ZIP_IMAGE',		ROOT . DIR_STATIC . 'zip.png', true);
	
	// If for some reason the folders and static HTML index files aren't in the current working direcotry,
	// enter the directory path here. Otherwise, keep it false.
	define('ROOT_FILE',		false, true);
	
	define('POST_URL',		ROOT . 'post.php', true);
	define('FILE_INDEX',	'index.html', true);
	define('FILE_PAGE',		'%d.html', true);
	define('FILE_MOD',		'mod.php', true);
	
	// Multi-board (%s is board abbreviation)
	define('BOARD_PATH', '%s/', true);
	
	// The HTTP status code to use when redirecting.
	// Should be 3xx (redirection). http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
	// "302" is recommended.
	define('REDIRECT_HTTP',	302, true);
	
	// Robot stuff
	// Strip repeating characters when making hashes
	define('ROBOT_ENABLE', true, true);
	define('ROBOT_BOARD', 'r9k', true);
	define('ROBOT_STRIP_REPEATING', true, true);
	// Enable mutes
	define('ROBOT_MUTE', true, true);
	define('ROBOT_MUTE_MIN', 60, true);
	define('ROBOT_MUTE_MAX', 120, true);
	define('ROBOT_MUTE_DESCRIPTION', 'You have been muted for unoriginal content.', true);
	
	/* 
		Mod stuff
	*/
	// Whether or not to lock moderator sessions to the IP address that was logged in with.
	define('MOD_LOCK_IP',	true,	true);
	// The page that is first shown when a moderator logs in. Defaults to the dashboard.
	define('MOD_DEFAULT',	'/',	true);
	// Don't even display MySQL password to administrators (in the configuration page)
	define('MOD_NEVER_REAL_PASSWORD', true, true);
	// Do a DNS lookup on IP addresses to get their hostname on the IP summary page
	define('MOD_DNS_LOOKUP', true, true);
	// Show ban form on the IP summary page
	define('MOD_IP_BANFORM', true, true);
	// How many recent posts, per board, to show in the IP summary page
	define('MOD_IP_RECENTPOSTS', 5, true);
	
	// Probably best not to change these:
	define('MOD_JANITOR',	0,		true);
	define('MOD_MOD',		1,		true);
	define('MOD_ADMIN',		2,		true);
	
	// Permissions
	// What level of administration you need to:
	
	/* Post Controls */
	// View IP addresses
	define('MOD_SHOW_IP', MOD_MOD, true);
	// Delete a post
	define('MOD_DELETE', MOD_JANITOR, true);
	// Ban a user for a post
	define('MOD_BAN', MOD_MOD, true);
	// Ban and delete (one click; instant)
	define('MOD_BANDELETE', MOD_BAN, true);
	// Delete file (and keep post)
	define('MOD_DELETEFILE', MOD_JANITOR, true);
	// Delete all posts by IP
	define('MOD_DELETEBYIP', MOD_BAN, true);
	// Sticky a thread
	define('MOD_STICKY', MOD_MOD, true);
	// Lock a thread
	define('MOD_LOCK', MOD_MOD, true);
	// Post in a locked thread
	define('MOD_POSTINLOCKED', MOD_MOD, true);
	// Post bypass unoriginal content check
	define('MOD_POSTUNORIGINAL', MOD_MOD, true);
	
	/* Administration */
	// Display the contents of instant-config.php
	define('MOD_SHOW_CONFIG', MOD_ADMIN, true);
	// Create a new board
	define('MOD_NEWBOARD', MOD_ADMIN, true);
	
	// Mod links (full HTML)
	// Correspond to above permission directives
	define('MOD_LINK_DELETE', '[D]', true);
	define('MOD_LINK_BAN', '[B]', true);
	define('MOD_LINK_BANDELETE', '[B&amp;D]', true);
	define('MOD_LINK_DELETEFILE', '[F]', true);
	define('MOD_LINK_DELETEBYIP', '[D+]', true);
	define('MOD_LINK_STICKY', '[Sticky]', true);
	define('MOD_LINK_DESTICKY', '[-Sticky]', true);
	define('MOD_LINK_LOCK', '[Lock]', true);
	define('MOD_LINK_UNLOCK', '[-Lock]', true);
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
	define('URL_REGEX',		'/' .	'(https?|ftp):\/\/' .	'(([\w\-]+\.)+[a-zA-Z]{2,6}|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})' .	'(\/([\w\-~\.#\/?=&;:+%]+)?)?' . '/', true);
	
	// Allowed file extensions
	$allowed_ext = Array('jpg', 'jpeg', 'bmp', 'gif', 'png', true);

	define('BUTTON_NEWTOPIC',	'New Topic', true);
	define('BUTTON_REPLY',		'New Reply', true);
	
	// The string passed to date() for post times
	// http://php.net/manual/en/function.date.php
	define('POST_DATE',			'm/d/y (D) H:i:s', true);
	
	define('ALWAYS_NOKO',		false, true);
	
	define('URL_MATCH',		'/^' .
		(preg_match(URL_REGEX, ROOT) ? '' :
			(@$_SERVER['HTTPS']?'https':'http') .
			':\/\/'.$_SERVER['HTTP_HOST']) .
			preg_quote(ROOT, '/') .
		'(' .
				str_replace('%s', '\w{1,8}', preg_quote(BOARD_PATH, '/')) .
			'|' .
				str_replace('%s', '\w{1,8}', preg_quote(BOARD_PATH, '/')) .
				preg_quote(FILE_INDEX, '/') .
			'|' .
				str_replace('%s', '\w{1,8}', preg_quote(BOARD_PATH, '/')) .
				str_replace('%d', '\d+', preg_quote(FILE_PAGE, '/')) .
			'|' .
				preg_quote(FILE_MOD, '/') .
			'\?\/.+' .
		')$/i', true);
	
	if(ROOT_FILE) {
		chdir(ROOT_FILE);
	}
	
	if(VERBOSE_ERRORS) {
		error_reporting(E_ALL);
		ini_set('display_errors', 1);
	}
?>