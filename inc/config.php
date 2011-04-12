<?php

/*
 *  WARNING: This is a project-wide configuration file shared by all Tinyboard users around the globe.
 *  If you would like to make instance-specific changes to your own setup, please use instance-config.php.
 *
 *  This is the default configuration. You can copy values from here and use them in
 * 	your instance-config.php
 *
 *  You can also create per-board configuration files. Once a board is created, locate its directory and
 *	create a new file named config.php (eg. b/config.php). Like instance-config.php, you can copy values
 *  from here and use them in your per-board configuration files.
 *
 */
	
	$config = Array(
		'db' => Array(),
		'cookies' => Array(),
		'error' => Array(),
		'dir' => Array(),
		'mod' => Array(),
		'spam' => Array(),
		'flood_filters' => Array(),
		'wordfilters' => Array(),
		'custom_capcode' => Array()
	);
	// Database stuff
	
	
	// SQL driver ("mysql", "pgsql", "sqlite", "dblib", etc)
	// http://www.php.net/manual/en/pdo.drivers.php
	$config['db']['type']		= 'mysql';
	// Hostname or IP address
	$config['db']['server']		= 'localhost';
	// Login
	$config['db']['user']		= '';
	$config['db']['password']	= '';
	// Tinyboard database
	$config['db']['database']	= '';
	// Anything more to add to the DSN string (eg. port=xxx;foo=bar)
	$config['db']['dsn']		= '';

	// The name of the session cookie (PHP's $_SESSION)
	$config['cookies']['session']= 'imgboard';
	
	// Used to safely determine when the user was first seen, to prevent floods. Contains a UNIX timestamp.
	$config['cookies']['time']	= 'arrived';
	// Contains an MD5 hash of $config['cookies']['time'] for verification.
	$config['cookies']['hash']	= 'hash';
	// Used for moderation login
	$config['cookies']['mod']	= 'mod';
	// Used for communicating with Javascript; telling it when posts were successful.
	// Rebuild Javascript file after changing this value or it won't work.
	$config['cookies']['js']	= 'serv';
	// Cookies "path". Defaults to $config['root']. If $config['root'] is a URL, you need to set this. Should be '/' or '/board/', depending on your installation.
	// $config['cookies']['path'] = '/';
	// Where to set the 'path' parameter to $config['cookies']['path'] when creating cookies. Recommended.
	$config['cookies']['jail']	= true;
	// How long should the cookies last (in seconds)
	$config['cookies']['expire']= 15778463; //6 months
	// Make this something long and random for security
	$config['cookies']['salt']	= 'wefaw98YHEWUFuo';
	// How long should moderators should remain logged in (0=browser session) (in seconds)
	$config['mod']['expire']	= 15778463; //6 months
	// Used to salt secure tripcodes (##trip)
	$config['secure_trip_salt']	= '@#$&^@#)$(*&@!_$(&329-8347';
	
	// How many seconds before you can post, after the first visit
	$config['lurktime']			= 30;
	// How many seconds between each post
	$config['flood_time']		= 10;
	// How many seconds between each post with exactly the same content and same IP
	$config['flood_time_ip']	= 120;
	// Same as above but different IP address
	$config['flood_time_same']	= 30;
	// Do you need a body for your non-OP posts?
	$config['force_body']		= false;
	// Reject blank posts? (just whitespace, etc)?
	$config['reject_blank']		= true;
	
	// Max body length
	$config['max_body']			= 1800;
	
	$config['threads_per_page']	= 10;
	$config['max_pages']		= 10;
	$config['threads_preview']	= 5;
	$config['threads_preview_sticky'] = 1;
	
	// For development purposes. Turns 'display_errors' on. Not recommended for production.
	$config['verbose_errors']	= true;

	// Error messages
	$config['error']['lurk']			= 'Lurk some more before posting.';
	$config['error']['bot']				= 'You look like a bot.';
	$config['error']['toolong']			= 'The %s field was too long.';
	$config['error']['toolong_body']	= 'The body was too long.';
	$config['error']['tooshort_body']	= 'The body was too short or empty.';
	$config['error']['noimage']			= 'You must upload an image.';
	$config['error']['nomove']			= 'The server failed to handle your upload.';
	$config['error']['fileext']			= 'Unsupported image format.';
	$config['error']['noboard']			= 'Invalid board!';
	$config['error']['nonexistant']		= 'Thread specified does not exist.';
	$config['error']['locked']			= 'Thread locked. You may not reply at this time.';
	$config['error']['nopost']			= 'You didn\'t make a post.';
	$config['error']['flood']			= 'Flood detected; Post discarded.';
	$config['error']['spam']			= 'Your request looks automated; Post discarded.';
	$config['error']['unoriginal']		= 'Unoriginal content!';
	$config['error']['muted']			= 'Unoriginal content! You have been muted for %d seconds.';
	$config['error']['youaremuted']		= 'You are muted! Expires in %d seconds.';
	$config['error']['tor']				= 'Hmm… That looks like a Tor exit node.';
	$config['error']['toomanylinks']	= 'Too many links; flood detected.';
	$config['error']['toomanycites']	= 'Too many cites; post discarded.';
	$config['error']['toomanycross']	= 'Too many cross-board links; post discarded.';
	$config['error']['nodelete']		= 'You didn\'t select anything to delete.';
	$config['error']['noreport']		= 'You didn\'t select anything to report.';
	$config['error']['toomanyreports']	= 'You can\'t report that many posts at once.';
	$config['error']['invalidpassword']	= 'Wrong password…';
	$config['error']['invalidimg']		= 'Invalid image.';
	$config['error']['filesize']		= 'Maximum file size: %maxsz% bytes<br>Your file\'s size: %filesz% bytes';
	$config['error']['maxsize']			= 'The file was too big.';
	$config['error']['invalidzip']		= 'Invalid archive!';
	$config['error']['fileexists']		= 'That file <a href="%s">already exists</a>!';
	$config['error']['delete_too_soon']	= 'You\'ll have to wait another %s before deleting that.';
	$config['error']['mime_exploit']	= 'MIME type detection XSS exploit (IE) detected; post discarded.';
	
	// Moderator errors
	$config['error']['invalid']			= 'Invalid username and/or password.';
	$config['error']['notamod']			= 'You are not a mod…';
	$config['error']['invalidafter']	= 'Invalid username and/or password. Your user may have been deleted or changed.';
	$config['error']['malformed']		= 'Invalid/malformed cookies.';
	$config['error']['missedafield']	= 'Your browser didn\'t submit an input when it should have.';
	$config['error']['required']		= 'The %s field is required.';
	$config['error']['invalidfield']	= 'The %s field was invalid.';
	$config['error']['boardexists']		= 'There is already a %s board.';
	$config['error']['noaccess']		= 'You don\'t have permission to do that.';
	$config['error']['invalidpost']		= 'That post doesn\'t exist…';
	$config['error']['404']				= 'Page not found.';
	$config['error']['modexists']		= 'That mod <a href="?/users/%d">already exists</a>!';
	
	// How many reports you can create in the same request.
	$config['report_limit']	= 2;
	
	// How long before you can delete a post after posting, in seconds.
	$config['delete_time']	= 10;
	
	// Reply limit (stops bumping thread when this is reached)
	$config['reply_limit']	= 250;
	
	// Strip superfluous new lines at the end of a post
	$config['strip_superfluous_returns'] = true;
	
	// For resizing, max values
	$config['thumb_width']	= 255;
	$config['thumb_height']	= 255;
	
	// When a thumbnailed image is going to be the same (in dimension), just copy the entire file and use that as a thumbnail instead of resizing/redrawing
	$config['minimum_copy_resize'] = true;
	
	// Store image hash in the database for r9k-like boards implementation soon
	// Function name for hashing
	// sha1_file, md5_file, etc.
	$config['file_hash']	= 'sha1_file';
	
	$config['block_tor']	= true;
	// Typically spambots try to post a lot of links. Refuse a post with X standalone links?
	$config['max_links']	= 20;
	// Maximum number of cites per post (protects against abuse)
	$config['max_cites']	= 45;
	// Maximum number of cross-board links/cites per post
	$config['max_cross']	= $config['max_cites'];
	
	// Maximum image upload size in bytes
	$config['max_filesize']	= 10*1024*1024; // 10MB
	// Maximum image dimensions
	$config['max_width']	= 10000;
	$config['max_height']	= $config['max_width']; // 1:1
	// Reject a post if its image is already in active content
	$config['image_reject_repost']	= true;
	/**
		Redraw the image using GD functions to strip any excess data (commonly ZIP archives)
		WARNING: Currently strips animated GIFs too :(
	**/
	$config['redraw_image']	= false;
	// Redrawing configuration
	$config['jpeg_quality']	= 100;
	// Temporary fix for the animation-stripping bug
	$config['redraw_gifs']	= false;
	
	// Display the aspect ratio in a post's file info
	$config['show_ratio']	= true;
	
	// The root directory, including the trailing slash, for Tinyboard.
	// examples: '/', 'http://boards.chan.org/', '/chan/'
	$config['root']		= ($_SERVER['REQUEST_URI'] == '/' ? '/' : str_replace('\\', '/', dirname($_SERVER['REQUEST_URI'])) . '/');
	
	$config['dir']['img']	= 'src/';
	$config['dir']['thumb']	= 'thumb/';
	$config['dir']['res']	= 'res/';
	// For load balancing, having a seperate server (and domain/subdomain) for serving static content is possible.
	// This can either be a directory or a URL (eg. http://static.example.org/)
	//$config['dir']['static']	= $config['root'] . 'static/';
	// Where to store the .html templates. This folder and templates must exist or fatal errors will be thrown.
	$config['dir']['template']	= getcwd() . '/templates';
	// Static images
	// These can be URLs OR base64 (data URI scheme)
	//$config['image_sticky']		= $config['dir']['static'] . 'sticky.gif';
	//$config['image_locked']		= $config['dir']['static'] . 'locked.gif';
	//$config['image_deleted']	= $config['dir']['static'] . 'deleted.png';
	//$config['image_zip']		= $config['dir']['static'] . 'zip.png';
	
	
	// If you want to put images and other dynamic-static stuff on another (preferably cookieless) domain, you can use this:
	// This will override $config['root'] and $config['dir']['...'] directives
	
	// "%s" will get replaced with $board['dir'], which usually includes a trailing slash. To avoid double slashes, you don't need
	// to put a slash after %s
	// $config['uri_thumb'] = 'http://images.example.org/%sthumb/';
	// $config['uri_img'] = 'http://images.example.org/%ssrc/';
	
	// If for some reason the folders and static HTML index files aren't in the current working direcotry,
	// enter the directory path here. Otherwise, keep it false.
	$config['root_file']	= false;
	
	$config['file_index']	= 'index.html';
	$config['file_page']	= '%d.html';
	$config['file_mod']		= 'mod.php';
	$config['file_post']	= 'post.php';
	$config['file_script']	= 'main.js';
	
	// Multi-board (%s is board abbreviation)
	$config['board_path']	= '%s/';
	
	// The HTTP status code to use when redirecting.
	// Should be 3xx (redirection). http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
	// "302" is recommended.
	$config['redirect_http']	= 302;
	
	// TODO: Put this in per-board instance-config instead
	// Robot stuff
	// Strip repeating characters when making hashes
	$config['robot_enable']				= false;
	$config['robot_strip_repeating']	= true;
	
	// Enable mutes
	// Tinyboard uses ROBOT9000's original 2^x implementation
	$config['robot_mute']		= true;
	// How many mutes x hours ago to include in the algorithm
	$config['robot_mute_hour']	= 336; // 2 weeks
	// If you want to alter the algorithm a bit. Default value is 2. n^x
	$config['robot_mute_multiplier']	= 2;
	$config['robot_mute_descritpion']	= 'You have been muted for unoriginal content.';
	
	/* 
		Mod stuff
	*/
	// Whether or not to lock moderator sessions to the IP address that was logged in with.
	$config['mod']['lock_ip'] = true;
	// The page that is first shown when a moderator logs in. Defaults to the dashboard.
	$config['mod']['default'] = '/';
	// Don't even display MySQL password to administrators (in the configuration page).
	$config['mod']['never_reveal_password'] = true;
	// Do a DNS lookup on IP addresses to get their hostname on the IP summary page
	$config['mod']['dns_lookup'] = true;
	// Show ban form on the IP summary page
	$config['mod']['ip_banform'] = true;
	// How many recent posts, per board, to show in the IP summary page
	$config['mod']['ip_recentposts'] = 5;
	// How many posts to display on the reports page
	$config['mod']['recent_reports'] = 5;
	// How many actions to show per page in the moderation log
	$config['mod']['modlog_page'] = 350;
	// Maximum number of results to display for a search, per board
	$config['mod']['search_results'] = 75;
	// Maximum number of notices to display on the moderator noticeboard
	$config['mod']['noticeboard_display'] = 50;
	// Number of entries to summarize and display on the dashboard
	$config['mod']['noticeboard_dashboard'] = 5;
	// Default public ban message
	$config['mod']['default_ban_message'] = 'USER WAS BANNED FOR THIS POST';
	// What to append to the post for public bans ("%s" is the message)
	$config['mod']['ban_message'] = '<span class="public_ban">(%s)</span>';
	// Wait indefinitely when rebuilding everything
	$config['mod']['rebuild_timelimit'] = 0;
	// Server-side confirm button for actions like deleting posts, for when Javascript is disabled or the DOM isn't loaded.
	$config['mod']['server-side_confirm'] = true;
	// PM snippet (for ?/inbox) length in characters
	$config['mod']['snippet_length'] = 75;
	
	// Probably best not to change these:
	if(!defined('JANITOR')) {
		define('JANITOR',	0,		true);
		define('MOD',		1,		true);
		define('ADMIN',		2,		true);
	}
	
	// Permissions
	// What level of administration you need to:
	
	/* Post Controls */
	// View IP addresses
	$config['mod']['show_ip'] = MOD;
	// Delete a post
	$config['mod']['delete'] = JANITOR;
	// Ban a user for a post
	$config['mod']['ban'] = MOD;
	// Ban and delete (one click; instant)
	$config['mod']['bandelete'] = MOD;
	// Remove bans
	$config['mod']['unban'] = MOD;
	// Delete file (and keep post)
	$config['mod']['deletefile'] = JANITOR;
	// Delete all posts by IP
	$config['mod']['deletebyip'] = MOD;
	// Sticky a thread
	$config['mod']['sticky'] = MOD;
	// Lock a thread
	$config['mod']['lock'] = MOD;
	// Post in a locked thread
	$config['mod']['postinlocked'] = MOD;
	// Post bypass unoriginal content check
	$config['mod']['postunoriginal'] = ADMIN;
	// Bypass flood check
	$config['mod']['flood'] = ADMIN;
	// Raw HTML posting
	$config['mod']['rawhtml'] = MOD;
	
	/* Administration */
	// Display the contents of instance-config.php
	$config['mod']['show_config'] = ADMIN;
	// View the report queue
	$config['mod']['reports'] = JANITOR;
	// Dismiss an abuse report
	$config['mod']['report_dismiss'] = JANITOR;
	// Dismiss all abuse reports by an IP
	$config['mod']['report_dismiss_ip'] = JANITOR;
	// View list of bans
	$config['mod']['view_banlist'] = MOD;
	// View the username of the mod who made a ban
	$config['mod']['view_banstaff'] = MOD;
	// If the moderator doesn't fit the $config['mod']['view_banstaff''] (previous) permission,
	// show him just a "?" instead. Otherwise, it will be "Mod" or "Admin"
	$config['mod']['view_banquestionmark'] = false;
	// Show expired bans in the ban list (they are kept in cache until the culprit returns)
	$config['mod']['view_banexpired'] = true;
	// View ban for IP address
	$config['mod']['view_ban'] = $config['mod']['view_banlist'];
	// View IP address notes
	$config['mod']['view_notes'] = JANITOR;
	// Create notes
	$config['mod']['create_notes'] = $config['mod']['view_notes'];
	// Remote notes
	$config['mod']['remove_notes'] = ADMIN;
	// Create a new board
	$config['mod']['newboard'] = ADMIN;
	// Manage existing boards (change title, etc)
	$config['mod']['manageboards'] = ADMIN;
	// Delete a board
	$config['mod']['deleteboard'] = ADMIN;
	// List/manage users
	$config['mod']['manageusers'] = MOD;
	// Promote/demote users
	$config['mod']['promoteusers'] = ADMIN;
	// Edit any users' login information
	$config['mod']['editusers'] = ADMIN;
	// Change user's own password
	$config['mod']['change_password'] = JANITOR;
	// Delete a user
	$config['mod']['deleteusers'] = ADMIN;
	// Create a user
	$config['mod']['createusers'] = ADMIN;
	// View the moderation log
	$config['mod']['modlog'] = ADMIN;
	// Create a PM (viewing mod usernames)
	$config['mod']['create_pm'] = JANITOR;
	// Read any PM, sent to or from anybody
	$config['mod']['master_pm'] = ADMIN;
	// Rebuild everything
	$config['mod']['rebuild'] = ADMIN;
	// Search through posts
	$config['mod']['search'] = JANITOR;
	// Read the moderator noticeboard
	$config['mod']['noticeboard'] = JANITOR;
	// Post to the moderator noticeboard
	$config['mod']['noticeboard_post'] = MOD;
	// Delete entries from the noticeboard
	$config['mod']['noticeboard_delete'] = ADMIN;
	// Public ban messages; attached to posts
	$config['mod']['public_ban'] = MOD;
	
	// Mod links (full HTML)
	// Correspond to above permission directives
	$config['mod']['link_delete'] = '[D]';
	$config['mod']['link_ban'] = '[B]';
	$config['mod']['link_bandelete'] = '[B&amp;D]';
	$config['mod']['link_deletefile'] = '[F]';
	$config['mod']['link_deletebyip'] = '[D+]';
	$config['mod']['link_sticky'] = '[Sticky]';
	$config['mod']['link_desticky'] = '[-Sticky]';
	$config['mod']['link_lock'] = '[Lock]';
	$config['mod']['link_unlock'] = '[-Lock]';
	
	// Moderator capcodes
	$config['capcode'] = ' <a class="capcode">## %s</a>';
	
	// Custom capcodes, by example:
	// "## Custom" becomes lightgreen, italic and bold
	//$config['custom_capcode']['Custom'] ='<a class="capcode" style="color:lightgreen;font-style:italic;font-weight:bold"> ## %s</a>';
	
	// "## Mod" makes everything purple, including the name and tripcode
	//$config['custom_capcode']['Mod'] = Array(
	//	'<a class="capcode" style="color:purple"> ## %s</a>',
	//	'color:purple', // Change name style; optional
	//	'color:purple' // Change tripcode style; optional
	//);
	
	// "## Admin" makes everything red and bold, including the name and tripcode
	//$config['custom_capcode']['Admin'] = Array(
	//	'<a class="capcode" style="color:red;font-weight:bold"> ## %s</a>',
	//	'color:red;font-weight:bold', // Change name style; optional
	//	'color:red;font-weight:bold' // Change tripcode style; optional
	//);
	
	// Spam filter
	$config['spam']['hidden_inputs_min']	= 4;
	$config['spam']['hidden_inputs_max']	= 12;
	// These are fields used to confuse the bots. Make sure they aren't actually used by Tinyboard, or it won't work.
	$config['spam']['hidden_input_names'] = Array(
		'user',
		'username',
		'login',
		'search',
		'q',
		'url',
		'firstname',
		'lastname',
		'text',
		'message'
	);
	// Always update this when adding new valid fields to the post form, or EVERYTHING WILL BE DETECTED AS SPAM!
	$config['spam']['valid_inputs'] = Array(
		'hash',
		'board',
		'thread',
		'mod',
		'name',
		'email',
		'subject',
		'post',
		'body',
		'password',
		'sticky',
		'lock',
		'raw'
	);
	
	// Custom flood filters. Detect flood attacks and reject new posts if there's a positive match.
	// See http://tinyboard.org/wiki/index.php?title=Flood_filters for more information.
	//$config['flood_filters'][] = Array(
	//	'condition' => Array(
	//		// 100 posts in the past 5 minutes (~20 p/m)
	//		'posts_in_past_x_minutes' => Array(100, 5)
	//	),
	//	// Don't allow the user to post
	//	'action' => 'reject',
	//	// Display this message
	//	'message' => 'Your post has been rejected on the suspicion of a flood attack on this board.'
	//);
	
	// Another filter
	//$config['flood_filters'][] = Array(
	//	'condition' => Array(
	//		// 10 new empty threads in the past 2 minutes
	//		'threads_with_no_replies_in_past_x_minutes' => Array(10, 2),
	//		// Allow replies, but not new threads (ie. reject topics only).
	//		'OP' => true
	//	),
	//	'action' => 'reject',
	//	'message' => 'Your post has been rejected on the suspicion of a flood attack on this board (too many new threads); post a reply instead.'
	//);
	
	// Wordfilters are used to automatically replace certain words/phrases with something else.
	
	// For a normal string replacement:
	// $config['wordfilters'][] = Array('cat', 'dog');
	
	// Advanced raplcement (regular expressions):
	// $config['wordfilters'][] = Array('/cat/', 'dog', true); // 'true' means it's a regular expression
	
	// A small file in the main directory indicating that the script has been ran and the board(s) have been generated.
	// This keeps the script from querying the database and causing strain when not needed.
	$config['has_installed'] = '.installed';
	
	// Name of the boards. Usually '/%s/' (/b/, /mu/, etc)
	// $config['board_abbreviation'] - BOARD_TITLE
	$config['board_abbreviation'] = '/%s/';
	
	// Automatically convert things like "..." to Unicode characters ("…")
	$config['auto_unicode']	= true;
	// Use some Wiki-like syntax (''em'', '''strong''', ==Heading==, etc)
	$config['wiki_markup']	= true;
	// Whether to turn URLs into functional links
	$config['markup_urls']	= true;
	// Complex regular expression to catch URLs
	$config['url_regex']	= '/' .	'(https?|ftp):\/\/' .	'(([\w\-]+\.)+[a-zA-Z]{2,6}|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})' .	'(\/([\w\-~.#\/?=&;:+%!*\[\]@$\'()+,]+)?)?' . '/';
	// INSANE regular expression for IPv6 addresses
	$config['ipv6_regex']	= '((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?';
	// Make IPv4 addresses look like IPv4 addresses ("::ffff:8.8.8.8" becomes "8.8.8.8")
	$config['ipv6_ipv4']	= true;
	// Regular expression to check for IE MIME type detection XSS exploit. To disable, comment the line out
	// https://github.com/savetheinternet/Tinyboard/issues/20
	$config['ie_mime_type_detection'] = '/<(?:body|head|html|img|plaintext|pre|script|table|title|a href|channel|scriptlet)/';
	
	// Allowed file extensions
	$config['allowed_ext'] = Array('jpg', 'jpeg', 'bmp', 'gif', 'png');
	
	// The names on the post buttons. (On most imageboards, these are both "Post".)
	$config['button_newtopic']	= 'New Topic';
	$config['button_reply']		= 'New Reply';
	
	// The default name (ie. Anonymous)
	$config['anonymous']	= 'Anonymous';
	
	// The string passed to date() for post times
	// http://php.net/manual/en/function.date.php
	$config['post_date']	= 'm/d/y (D) H:i:s';
	
	// Always act as if they had typed "noko" in the email field no mattter what
	$config['always_noko']	= false;
	
	// Characters used to generate a random password (with Javascript)
	$config['genpassword_chars'] = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+';
	
	// Enable IP range bans (eg. "127.*.0.1", "127.0.0.*", and "12*.0.0.1" all match "127.0.0.1").
	// A little more load on the database
	$config['ban_range']	= true;
	
	// Custom stylesheets available. The prefix for each stylesheet URI is defined below.
	$config['stylesheets'] = Array(
		// Stylesheet name => URI
		'Yotsuba B' => 'default.css',
		'Yotsuba' => 'yotsuba.css'
	);
	
	// The prefix for each stylesheet URI. Defaults to $config['root']
	//$config['uri_stylesheets'] = 'http://static.example.org/stylesheets/';
	
	// The default stylesheet to use
	$config['default_stylesheet'] = Array('Yotsuba B', $config['stylesheets']['Yotsuba B']);
	
	// Boardlinks
	// You can group, order and place the boardlist at the top of every page, using the following template.	
	//$config['boards'] = Array(
	//	Array('a', 'b'),
	//	Array('c', 'd', 'e', 'f', 'g'),
	//	Array('h', 'i', 'j'),
	//	Array('k', Array('l', 'm')),
	// 'status' => 'http://status.example.org/'
	//);
	
	// Set custom locations for stylesheets, scripts and maybe a banner.
	// This can be good for load balancing across multiple servers or hostnames.
	// $config['url_stylesheet'] = 'http://static.example.org/style.css';
	// $config['url_javascript'] = 'http://static.example.org/main.js';
	// $config['url_banner'] = '/banner.php';
	// $config['url_favicon'] = '/favicon.gif';
	
	// Meta keywords. It's probably best to include these in per-board configurations.
	//$config['meta_keywords'] = 'chan,anonymous discussion,imageboard,tinyboard';
	
	// Link imageboard to your Google Analytics account to track users and provide marketing insights.
	// $config['google_analytics'] = 'UA-xxxxxxx-yy';
?>