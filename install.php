<?php
	// Installation/upgrade file	
	define('VERSION', 'v0.9.5-dev-2');
	
	require 'inc/functions.php';
	require 'inc/display.php';
	require 'inc/template.php';
	require 'inc/database.php';
	require 'inc/user.php';
	$step = isset($_GET['step']) ? round($_GET['step']) : 0;
	$page = Array(
		'config' => $config,
		'title' => 'Install',
		'body' => '',
		'nojavascript' => true
	);
	
	// this breaks the dispaly of licenses if enabled
	$config['minify_html'] = false;
	
	if(file_exists($config['has_installed'])) {
		
		// Check the version number
		$version = trim(file_get_contents($config['has_installed']));
		if(empty($version))
			$version = 'v0.9.1';
		
		$boards = listBoards();
		
		switch($version) {
			case 'v0.9':
			case 'v0.9.1':
				// Upgrade to v0.9.2-dev
				
				foreach($boards as &$_board) {
					// Add `capcode` field after `trip`
					query(sprintf("ALTER TABLE `posts_%s` ADD  `capcode` VARCHAR( 50 ) NULL AFTER  `trip`", $_board['uri'])) or error(db_error());
					
					// Resize `trip` to 15 characters
					query(sprintf("ALTER TABLE `posts_%s` CHANGE  `trip`  `trip` VARCHAR( 15 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL", $_board['uri'])) or error(db_error());
				}
			case 'v0.9.2-dev':
				// Upgrade to v0.9.2-dev-1
				
				// New table: `theme_settings`
				query("CREATE TABLE IF NOT EXISTS `theme_settings` ( `name` varchar(40) NOT NULL, `value` text, UNIQUE KEY `name` (`name`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;") or error(db_error());
				
				// New table: `news`
				query("CREATE TABLE IF NOT EXISTS `news` ( `id` int(11) NOT NULL AUTO_INCREMENT, `name` text NOT NULL, `time` int(11) NOT NULL, `subject` text NOT NULL, `body` text NOT NULL, UNIQUE KEY `id` (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;") or error(db_error());
			case 'v0.9.2.1-dev':
			case 'v0.9.2-dev-1':
				// Fix broken version number/mistake
				$version = 'v0.9.2-dev-1';
				// Upgrade to v0.9.2-dev-2
				
				foreach($boards as &$_board) {
					// Increase field sizes
					query(sprintf("ALTER TABLE `posts_%s` CHANGE  `subject` `subject` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL", $_board['uri'])) or error(db_error());
					query(sprintf("ALTER TABLE `posts_%s` CHANGE  `name` `name` VARCHAR( 35 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL", $_board['uri'])) or error(db_error());
				}
			case 'v0.9.2-dev-2':
				// Upgrade to v0.9.2-dev-3 (v0.9.2)
				
				foreach($boards as &$_board) {
					// Add `custom_fields` field
					query(sprintf("ALTER TABLE `posts_%s` ADD `embed` TEXT NULL", $_board['uri'])) or error(db_error());
				}
			case 'v0.9.2-dev-3': // v0.9.2-dev-3 == v0.9.2
			case 'v0.9.2':
				// Upgrade to v0.9.3-dev-1
				
				// Upgrade `theme_settings` table
				query("TRUNCATE TABLE `theme_settings`") or error(db_error());
				query("ALTER TABLE  `theme_settings` ADD  `theme` VARCHAR( 40 ) NOT NULL FIRST") or error(db_error());
				query("ALTER TABLE  `theme_settings` CHANGE  `name`  `name` VARCHAR( 40 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL") or error(db_error());
				query("ALTER TABLE  `theme_settings` DROP INDEX  `name`") or error(db_error());
			case 'v0.9.3-dev-1':
				query("ALTER TABLE  `mods` ADD  `boards` TEXT NOT NULL") or error(db_error());
				query("UPDATE `mods` SET `boards` = '*'") or error(db_error());
			case 'v0.9.3-dev-2':
				foreach($boards as &$_board) {
					query(sprintf("ALTER TABLE `posts_%s` CHANGE `filehash`  `filehash` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL", $_board['uri'])) or error(db_error());
				}
			case 'v0.9.3-dev-3':
				// Board-specifc bans
				query("ALTER TABLE `bans` ADD  `board` SMALLINT NULL AFTER  `reason`") or error(db_error());
			case 'v0.9.3-dev-4':
				// add ban ID
				query("ALTER TABLE `bans` ADD  `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY ( `id` ), ADD UNIQUE (`id`)");
			case 'v0.9.3-dev-5':
				foreach($boards as &$_board) {
					// Increase subject field size
					query(sprintf("ALTER TABLE `posts_%s` CHANGE  `subject` `subject` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL", $_board['uri'])) or error(db_error());
				}
			case 'v0.9.3-dev-6':
				// change to MyISAM
				$tables = Array(
					'bans', 'boards', 'ip_notes', 'modlogs', 'mods', 'mutes', 'noticeboard', 'pms', 'reports', 'robot', 'theme_settings', 'news'
				);
				foreach($boards as &$board) {
					$tables[] = "posts_{$board['uri']}";
				}
				
				foreach($tables as &$table) {
					query("ALTER TABLE  `{$table}` ENGINE = MYISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci") or error(db_error());
				}
			case 'v0.9.3-dev-7':
				foreach($boards as &$board) {
					query(sprintf("ALTER TABLE  `posts_%s` CHANGE  `filename` `filename` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL", $board['uri'])) or error(db_error());
				}
			case 'v0.9.3-dev-8':
				foreach($boards as &$board) {
					query(sprintf("ALTER TABLE `posts_%s` ADD INDEX (  `thread` )", $board['uri'])) or error(db_error());
				}
			case 'v0.9.3-dev-9':
				foreach($boards as &$board) {
					query(sprintf("ALTER TABLE `posts_%s`ADD INDEX (  `time` )", $board['uri'])) or error(db_error());
					query(sprintf("ALTER TABLE `posts_%s`ADD FULLTEXT (`body`)", $board['uri'])) or error(db_error());
				}
			case 'v0.9.3-dev-10':
			case 'v0.9.3':
				query("ALTER TABLE  `bans` DROP INDEX `id`") or error(db_error());
				query("ALTER TABLE  `pms` DROP INDEX  `id`") or error(db_error());
				query("ALTER TABLE  `boards` DROP PRIMARY KEY") or error(db_error());
				query("ALTER TABLE  `reports` DROP INDEX  `id`") or error(db_error());
				query("ALTER TABLE  `boards` DROP INDEX `uri`") or error(db_error());
				
				query("ALTER IGNORE TABLE  `robot` ADD PRIMARY KEY (`hash`)") or error(db_error());
				query("ALTER TABLE  `bans` ADD FULLTEXT (`ip`)") or error(db_error());
				query("ALTER TABLE  `ip_notes` ADD INDEX (`ip`)") or error(db_error());	
				query("ALTER TABLE  `modlogs` ADD INDEX (`time`)") or error(db_error());
				query("ALTER TABLE  `boards` ADD PRIMARY KEY(`uri`)") or error(db_error());
				query("ALTER TABLE  `mutes` ADD INDEX (`ip`)") or error(db_error());
				query("ALTER TABLE  `news` ADD INDEX (`time`)") or error(db_error());
				query("ALTER TABLE  `theme_settings` ADD INDEX (`theme`)") or error(db_error());
			case 'v0.9.4-dev-1':
				foreach($boards as &$board) {
					query(sprintf("ALTER TABLE  `posts_%s` ADD  `sage` INT( 1 ) NOT NULL AFTER  `locked`", $board['uri'])) or error(db_error());
				}
			case 'v0.9.4-dev-2':
				if(!isset($_GET['confirm'])) {
					$page['title'] = 'License Change';
					$page['body'] = '<p style="text-align:center">You are upgrading to a version which uses an amended license. The licenses included with Tinyboard distributions prior to this version (v0.9.4-dev-2) are still valid for those versions, but no longer apply to this and newer versions.</p>' .
						'<textarea style="width:700px;height:370px;margin:auto;display:block;background:white;color:black" disabled>' . htmlentities(file_get_contents('LICENSE.md')) . '</textarea>
						<p style="text-align:center">
							<a href="?confirm=1">I have read and understood the agreement. Proceed to upgrading.</a>
						</p>';
					
					file_write($config['has_installed'], 'v0.9.4-dev-2');
					
					break;
				}
			case 'v0.9.4-dev-3':
			case 'v0.9.4-dev-4':
			case 'v0.9.4':
				foreach($boards as &$board) {
					query(sprintf("ALTER TABLE  `posts_%s`
						CHANGE `subject` `subject` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
						CHANGE  `email`  `email` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
						CHANGE  `name`  `name` VARCHAR( 35 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL", $board['uri'])) or error(db_error());
				}
			case 'v0.9.5-dev-1':
				foreach($boards as &$board) {
					query(sprintf("ALTER TABLE  `posts_%s` ADD  `body_nomarkup` TEXT NULL AFTER  `body`", $board['uri'])) or error(db_error());
				}
				query("CREATE TABLE IF NOT EXISTS `cites` (  `board` varchar(8) NOT NULL,  `post` int(11) NOT NULL,  `target_board` varchar(8) NOT NULL,  `target` int(11) NOT NULL,  KEY `target` (`target_board`,`target`),  KEY `post` (`board`,`post`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;") or error(db_error());
			case false:
				// Update version number
				file_write($config['has_installed'], VERSION);
				
				$page['title'] = 'Upgraded';
				$page['body'] = '<p style="text-align:center">Successfully upgraded from ' . $version . ' to <strong>' . VERSION . '</strong>.</p>';
				break;
			default:
				$page['title'] = 'Unknown version';
				$page['body'] = '<p style="text-align:center">Tinyboard was unable to determine what version is currently installed.</p>';
				break;
			case VERSION:
				$page['title'] = 'Already installed';
				$page['body'] = '<p style="text-align:center">It appears that Tinyboard is already installed (' . $version . ') and there is nothing to upgrade! Delete <strong>' . $config['has_installed'] . '</strong> to reinstall.</p>';
				break;
		}			
		
		die(Element('page.html', $page));
	}
	
	if($step == 0) {
		// Agreeement
		$page['body'] = '
		<textarea style="width:700px;height:370px;margin:auto;display:block;background:white;color:black" disabled>' . htmlentities(file_get_contents('LICENSE.md')) . '</textarea>
		<p style="text-align:center">
			<a href="?step=1">I have read and understood the agreement. Proceed to installation.</a>
		</p>';
		
		echo Element('page.html', $page);
	} elseif($step == 1) {
		$page['title'] = 'Pre-installation test';
		
		$page['body'] = '<table class="test">';
		
		function rheader($item) {
			global $page, $config;
			
			$page['body'] .= '<tr class="h"><th colspan="2">' . $item . '</th></tr>';
		}
		
		function row($item, $result) {
			global $page, $config, $__is_error;
			if(!$result)
				$__is_error = true;
			$page['body'] .= '<tr><th>' . $item . '</th><td><img style="width:16px;height:16px" src="' . $config['dir']['static'] . ($result ? 'ok.png' : 'error.png') . '" /></td></tr>';
		}
		
		
		// Required extensions
		rheader('PHP extensions');
		row('PDO', extension_loaded('pdo'));
		row('GD', extension_loaded('gd'));
		
		// GD tests
		rheader('GD tests');
		row('JPEG', function_exists('imagecreatefromjpeg'));
		row('PNG', function_exists('imagecreatefrompng'));
		row('GIF', function_exists('imagecreatefromgif'));
		
		// Database drivers
		$drivers = PDO::getAvailableDrivers();
		
		rheader('PDO drivers <em>(currently installed drivers)</em>');
		foreach($drivers as &$driver) {
			row($driver, true);
		}
		
		// Permissions
		rheader('File permissions');
		row('<em>root directory</em> (' . getcwd() . ')', is_writable('.'));
		
		$page['body'] .= '</table>
		<p style="text-align:center">
			<a href="?step=2"' .
				(isset($__is_error) ? ' onclick="return confirm(\'Are you sure you want to continue when errors exist?\')"' : '') .
			'>Continue' . (isset($__is_error) ? ' anyway' : '') . '</a>
		</p>';
		
		echo Element('page.html', $page);
	} elseif($step == 2) {
		// Basic config
		$page['title'] = 'Configuration';
		
		function create_salt() {
			return substr(base64_encode(sha1(rand())), 0, rand(25, 31));
		}
		
		$page['body'] = '
	<form action="?step=3" method="post">
		<fieldset>
		<legend>Database</legend>
			<label for="db_type">Type:</label> 
			<select id="db_type" name="db[type]">';
			
			$drivers = PDO::getAvailableDrivers();
			
			foreach($drivers as &$driver) {
				$driver_txt = $driver;
				switch($driver) {
					case 'cubrid':
						$driver_txt = 'Cubrid';
						break;
					case 'dblib':
						$driver_txt = 'FreeTDS / Microsoft SQL Server / Sybase';
						break;
					case 'firebird':
						$driver_txt = 'Firebird/Interbase 6';
						break;
					case 'ibm':
						$driver_txt = 'IBM DB2';
						break;
					case 'informix':
						$driver_txt = 'IBM Informix Dynamic Server';
						break;
					case 'mysql':
						$driver_txt = 'MySQL';
						break;
					case 'oci':
						$driver_txt = 'OCI';
						break;
					case 'odbc':
						$driver_txt = 'ODBC v3 (IBM DB2, unixODBC)';
						break;
					case 'pgsql':
						$driver_txt = 'PostgreSQL';
						break;
					case 'sqlite':
						$driver_txt = 'SQLite 3';
						break;
					case 'sqlite2':
						$driver_txt = 'SQLite 2';
						break;
				}
				$page['body'] .= '<option value="' . $driver . '">' . $driver_txt . '</option>';
			}
			
			$page['body'] .= '	
			</select>
			
			<label for="db_server">Server:</label> 
			<input type="text" id="db_server" name="db[server]" value="localhost" />
			
			<label for="db_db">Database:</label> 
			<input type="text" id="db_db" name="db[database]" value="" />
			
			<label for="db_user">Username:</label> 
			<input type="text" id="db_user" name="db[user]" value="" />
			
			<label for="db_pass">Password:</label> 
			<input type="password" id="db_pass" name="db[password]" value="" />
		</fieldset>
		<p style="text-align:center" class="unimportant">The following is all later configurable. For more options, <a href="http://tinyboard.org/docs/config_basics.html">edit your configuration files</a> after installing.</p>
		<fieldset>
		<legend>Cookies</legend>
			<label for="cookies_mod">Moderator cookie:</label> 
			<input type="text" id="cookies_mod" name="cookies[mod]" value="' . $config['cookies']['mod'] . '" />
			
			<label for="cookies_salt">Secure salt:</label> 
			<input type="text" id="cookies_salt" name="cookies[salt]" value="' . create_salt() . '" size="40" />
		</fieldset>
		
		<fieldset>
		<legend>Flood control</legend>
			<label for="flood_time">Seconds before each post:</label> 
			<input type="text" id="flood_time" name="flood_time" value="' . $config['flood_time'] . '" />
			
			<label for="flood_time_ip">Seconds before you can repost something (post the exact same text):</label> 
			<input type="text" id="flood_time_ip" name="flood_time_ip" value="' . $config['flood_time_ip'] . '" />
			
			<label for="flood_time_same">Same as above, but with a different IP address:</label> 
			<input type="text" id="flood_time_same" name="flood_time_same" value="' . $config['flood_time_same'] . '" />
			
			<label for="max_body">Maximum post body length:</label> 
			<input type="text" id="max_body" name="max_body" value="' . $config['max_body'] . '" />
			
			<label for="reply_limit">Replies in a thread before it can no longer be bumped:</label> 
			<input type="text" id="reply_limit" name="reply_limit" value="' . $config['reply_limit'] . '" />
			
			<label for="max_links">Maximum number of links in a single post:</label> 
			<input type="text" id="max_links" name="max_links" value="' . $config['max_links'] . '" />			
		</fieldset>
		
		<fieldset>
		<legend>Images</legend>
			<label for="max_filesize">Maximum image filesize:</label> 
			<input type="text" id="max_filesize" name="max_filesize" value="' . $config['max_filesize'] . '" />
			
			<label for="thumb_width">Thumbnail width:</label> 
			<input type="text" id="thumb_width" name="thumb_width" value="' . $config['thumb_width'] . '" />
			
			<label for="thumb_height">Thumbnail height:</label> 
			<input type="text" id="thumb_height" name="thumb_height" value="' . $config['thumb_height'] . '" />
			
			<label for="max_width">Maximum image width:</label> 
			<input type="text" id="max_width" name="max_width" value="' . $config['max_width'] . '" />
			
			<label for="max_height">Maximum image height:</label> 
			<input type="text" id="max_height" name="max_height" value="' . $config['max_height'] . '" />
		</fieldset>
		
		<fieldset>
		<legend>Display</legend>
			<label for="threads_per_page">Threads per page:</label> 
			<input type="text" id="threads_per_page" name="threads_per_page" value="' . $config['threads_per_page'] . '" />
			
			<label for="max_pages">Page limit:</label> 
			<input type="text" id="max_pages" name="max_pages" value="' . $config['max_pages'] . '" />
			
			<label for="threads_preview">Number of replies to show per thread on the index page:</label> 
			<input type="text" id="threads_preview" name="threads_preview" value="' . $config['threads_preview'] . '" />
		</fieldset>
		
		<fieldset>
		<legend>Directories</legend>
			<label for="root">Root URI (include trailing slash):</label> 
			<input type="text" id="root" name="root" value="' . $config['root'] . '" />
			
			<label for="dir_img">Image directory:</label> 
			<input type="text" id="dir_img" name="dir[img]" value="' . $config['dir']['img'] . '" />
			
			<label for="dir_thumb">Thumbnail directory:</label> 
			<input type="text" id="dir_thumb" name="dir[thumb]" value="' . $config['dir']['thumb'] . '" />
			
			<label for="dir_res">Thread directory:</label> 
			<input type="text" id="dir_res" name="dir[res]" value="' . $config['dir']['res'] . '" />
		</fieldset>
		
		<fieldset>
		<legend>Miscellaneous</legend>
			<label for="secure_trip_salt">Secure trip (##) salt:</label> 
			<input type="text" id="secure_trip_salt" name="secure_trip_salt" value="' . create_salt() . '" size="40" />
		</fieldset>
		
		<p style="text-align:center">
			<input type="submit" value="Complete installation" />
		</p>
	</form>
		';
		
		
		echo Element('page.html', $page);
	} elseif($step == 3) {
		$instance_config = 
'<?php

/*
 *  Instance Configuration
 *  ----------------------
 *  Edit this file and not config.php for imageboard configuration.
 *
 *  You can copy values from config.php (defaults) and paste them here.
 */

';
		
		function create_config_from_array(&$instance_config, &$array, $prefix = '') {
			foreach($array as $name => $value) {
				if(is_array($value)) {
					$instance_config .= "\n";
					create_config_from_array($instance_config, $value, $prefix . '[\'' . addslashes($name) . '\']');
					$instance_config .= "\n";
				} else {
					$instance_config .= '	$config' . $prefix . '[\'' . addslashes($name) . '\'] = ';
					
					if(is_numeric($value))
						$instance_config .= $value;
					else
						$instance_config .= "'" . addslashes($value) . "'";
						
					$instance_config .= ";\n";
				}
			}
		}
		
		create_config_from_array($instance_config, $_POST);
		
		$instance_config .= "\n";
		
		if(@file_put_contents('inc/instance-config.php', $instance_config)) {
			header('Location: ?step=4', true, $config['redirect_http']);
		} else {
			$page['title'] = 'Manual installation required';
			$page['body'] = '
				<p>I couldn\'t write to <strong>inc/instance-config.php</strong> with the new configuration, probably due to a permissions error.</p>
				<p>Please complete the installation manually by copying and pasting the following code into the contents of <strong>inc/instance-config.php</strong>:</p>
				<textarea style="width:700px;height:370px;margin:auto;display:block;background:white;color:black">' . htmlentities($instance_config) . '</textarea>
				<p style="text-align:center">
					<a href="?step=4">Once complete, click here to complete installation.</a>
				</p>
			';
			echo Element('page.html', $page);
		}
	} elseif($step == 4) {
		// SQL installation
		
		buildJavascript();
		
		$sql = @file_get_contents('install.sql') or error("Couldn't load install.sql.");
		
		// This code is probably horrible, but what I'm trying
		// to do is find all of the SQL queires and put them
		// in an array.
		preg_match_all("/(^|\n)((SET|CREATE|INSERT).+)\n\n/msU", $sql, $queries);
		$queries = $queries[2];
		
		$queries[] = Element('posts.sql', Array('board' => 'b'));
		
		$sql_errors = '';
		foreach($queries as &$query) {
			if(!query($query))
				$sql_errors .= '<li>' . db_error() . '</li>';
		}
		
		$boards = listBoards();
		foreach($boards as &$_board) {
			setupBoard($_board);
			buildIndex();
		}
		
		$page['title'] = 'Installation complete';
		$page['body'] = '<p style="text-align:center">Thank you for using Tinyboard. Please remember to report any bugs you discover. <a href="http://tinyboard.org/docs/config_basics.html">How do I edit the config files?</a></p>';
		
		if(!empty($sql_errors)) {
			$page['body'] .= '<div class="ban"><h2>SQL errors</h2><p>SQL errors were encountered when trying to install the database. This may be the result of using a database which is already occupied with a Tinyboard installation; if so, you can probably ignore this.</p><p>The errors encountered were:</p><ul>' . $sql_errors . '</ul><p><a href="?step=5">Ignore errors and complete installation.</a></p></div>';
		} else {
			file_write($config['has_installed'], VERSION);
			if(!file_unlink(__FILE__)) {
				$page['body'] .= '<div class="ban"><h2>Delete install.php!</h2><p>I couldn\'t remove <strong>install.php</strong>. You will have to remove it manually.</p></div>';
			}
		}
		
		echo Element('page.html', $page);
	} elseif($step == 5) {
		$page['title'] = 'Installation complete';
		$page['body'] = '<p style="text-align:center">Thank you for using Tinyboard. Please remember to report any bugs you discover.</p>';
		
		file_write($config['has_installed'], VERSION);
		if(!file_unlink(__FILE__)) {
			$page['body'] .= '<div class="ban"><h2>Delete install.php!</h2><p>I couldn\'t remove <strong>install.php</strong>. You will have to remove it manually.</p></div>';
		}
		
		echo Element('page.html', $page);
	}
?>
