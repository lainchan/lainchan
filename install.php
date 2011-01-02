<?php

// install.php
// Basic installer written by Alpha.

// TODO:
//	* The installer should create the database
//	* Error checking
//	* General cleanup
//	* Skin

// Debug
error_reporting(E_ALL); 
ini_set("display_errors", 1);

function htmlclean ($string) {
	return htmlentities($string,ENT_QUOTES);
}

if (file_exists('inc/instance-config.php')) {
	echo "<h1>Error:</h1>";
	echo "<p>inc/instance-config.php already exists. Please delete it before trying to re-install.</p>";
	die();
}

$default_values = array(
		// Database stuff
		array(
			'output' => "<h2>Database settings</h2>\n".
			"<p>Please change the following to reflect the setup of your database.</p>"
		),
		array(
			'name' => 'DB_TYPE',
			'html' => 'dropdown',
			'values' => array('mysql'=>'MySQL'),
			'comment' => 'Database engine:'
		),
		array(
			'name' => 'DB_SERVER',
			'html' => 'text',
			'values' => 'localhost',
			'comment' => 'Database hostname:'
		),
		array(
			'name' => 'DB_USER',
			'html' => 'text',
			'values' => 'root',
			'comment' => 'Database username:'
		),
		array(
			'name' => 'DB_PASSWORD',
			'html' => 'password',
			'values' => '',
			'comment' => 'Database password:'
		),
		array(
			'name' => 'DB_DATABASE',
			'html' => 'text',
			'values' => 'tinyboard',
			'comment' => 'Database name (please create this database beforehand):'
		),
		
		// General Config
		array(
			'output' => "<h2>General Config</h2>\n".
			"<p>General board configuration.</p>"
		),
		array(
			'name' => 'LURKTIME',
			'html' => 'text',
			'values' => '30',
			'comment' => 'How many seconds before you can post, after the first visit:'
		),
		array(
			'name' => 'MAX_BODY',
			'html' => 'text',
			'values' => '1800',
			'comment' => 'Max body length:'
		),
		array(
			'name' => 'THREADS_PER_PAGE',
			'html' => 'text',
			'values' => '10',
			'comment' => 'Threads per page:'
		),
		array(
			'name' => 'MAX_PAGES',
			'html' => 'text',
			'values' => '5',
			'comment' => 'Max pages:'
		),
		array(
			'name' => 'THREADS_PREVIEW',
			'html' => 'text',
			'values' => '5',
			'comment' => 'Threads Preview:'
		),
		array(
			'name' => 'VERBOSE_ERRORS',
			'html' => 'bool',
			'values' => true,
			'comment' => 'Turns \'display_errors\' on. Not recommended for production.:'
		),
		
		// Image Config
		array(
			'output' => "<h2>Image Config</h2>\n".
			"<p>Image configuration.</p>"
		),
		array(
			'name' => 'THUMB_WIDTH',
			'html' => 'text',
			'values' => '200',
			'comment' => 'Maximum thumbnail width:'
		),
		array(
			'name' => 'THUMB_HEIGHT',
			'html' => 'text',
			'values' => '200',
			'comment' => 'Maximum thumbnail height:'
		),
		array(
			'name' => 'MAX_FILESIZE',
			'html' => 'text',
			'values' => '10485760',
			'comment' => 'Maximum file size (in bytes; default: 10MB):'
		),
		array(
			'name' => 'MAX_WIDTH',
			'html' => 'text',
			'values' => '10000',
			'comment' => 'Maximum image width:'
		),
		array(
			'name' => 'MAX_HEIGHT',
			'html' => 'text',
			'values' => '10000',
			'comment' => 'Maximum image height:'
		),
		array(
			'name' => 'ALLOW_ZIP',
			'html' => 'bool',
			'values' => false,
			'comment' => 'When you upload a ZIP as a file, all the images inside the archive '.
			'get dumped into the thread as replies. (Extremely beta and not recommended yet.)'
		),
		array(
			'name' => 'REDRAW_IMAGE',
			'html' => 'bool',
			'values' => false,
			'comment' => 'Redraw the image using GD functions to strip any excess data (WARNING: VERY BETA).'
		),
		array(
			'name' => 'SHOW_RATIO',
			'html' => 'bool',
			'values' => true,
			'comment' => 'Display the aspect ratio in a post\'s file info.'
		),
		
		// Cookies
		array(
			'output' => "<h2>Cookies</h2>\n".
			"<p>The following deals with cookie setup. ".
			"You probably don't need to change it.</p>"
		),
		array(
			'name' => 'SESS_COOKIE',
			'html' => 'text',
			'values' => 'imgboard',
			'comment' => 'Name of the session cookie:'
		),
		array(
			'name' => 'TIME_COOKIE',
			'html' => 'text',
			'values' => 'arrived',
			'comment' => 'Name of the time cookie:'
		),
		array(
			'name' => 'HASH_COOKIE',
			'html' => 'text',
			'values' => 'hash',
			'comment' => 'Name of the hash cookie:'
		),
		array(
			'name' => 'MOD_COOKIE',
			'html' => 'text',
			'values' => 'mod',
			'comment' => 'Name of the moderator cookie:'
		),
		array(
			'name' => 'JAIL_COOKIES',
			'html' => 'text',
			'values' => 'true',
			'comment' => 'Where to set the \'path\' parameter to ROOT when creating cookies. Recommended.:'
		),
		array(
			'name' => 'COOKIE_EXPIRE',
			'html' => 'text',
			'values' => '15778463',
			'comment' => 'How long should the cookies last (in seconds; default 6 months):'
		),
		array(
			'name' => 'SALT',
			'html' => 'text',
			'values' => md5(rand(0,100)),
			'comment' => 'Make this something long and random for security:'
		),
		
	);

$sql = array(
		'SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";',
		'CREATE TABLE IF NOT EXISTS `boards` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `uri` varchar(8) NOT NULL,
  `title` varchar(20) NOT NULL,
  `subtitle` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uri` (`uri`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;',
		'INSERT INTO `boards` (`id`, `uri`, `title`, `subtitle`) VALUES
(1, \'b\', \'Beta\', \'In development.\');',
		'CREATE TABLE IF NOT EXISTS `posts_b` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `thread` int(11) DEFAULT NULL,
  `subject` varchar(40) NOT NULL,
  `email` varchar(30) NOT NULL,
  `name` varchar(25) NOT NULL,
  `trip` varchar(15) DEFAULT NULL,
  `body` text NOT NULL,
  `time` int(11) NOT NULL,
  `bump` int(11) DEFAULT NULL,
  `thumb` varchar(50) DEFAULT NULL,
  `thumbwidth` int(11) DEFAULT NULL,
  `thumbheight` int(11) DEFAULT NULL,
  `file` varchar(50) DEFAULT NULL,
  `filewidth` int(11) DEFAULT NULL,
  `fileheight` int(11) DEFAULT NULL,
  `filesize` int(11) DEFAULT NULL,
  `filename` varchar(30) DEFAULT NULL,
  `filehash` varchar(32) DEFAULT NULL,
  `password` varchar(20) DEFAULT NULL,
  `ip` varchar(15) NOT NULL,
  `sticky` int(1) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;',
		'CREATE TABLE IF NOT EXISTS `mods` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `password` char(40) NOT NULL COMMENT \'SHA1\',
  `type` smallint(1) NOT NULL COMMENT \'0: janitor, 1: mod, 2: admin\',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`,`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;',
		'INSERT INTO `mods` (`id`, `username`, `password`, `type`) VALUES
(1, \'admin\', \'5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8\', 2);',
		'CREATE TABLE IF NOT EXISTS  `bans` (
  `ip` varchar( 15 ) NOT NULL ,
  `mod` int NOT NULL COMMENT  \'which mod made the ban\',
  `set` int NOT NULL,
  `expires` int NULL,
  `reason` text NULL
) ENGINE = InnoDB;',
	);

if (isset($_POST['submit'])) {
	$date = date('H:i jS F Y (e)');
	$config_file = "<?php\n// Installer Generated Config File\n// Created at $date\n";
	foreach ($default_values as $value) {
		if (isset($value['output'])) {
			continue;
		}
		if (isset($_POST[$value['name']])) {
			$x = $_POST[$value['name']];
		} else {
			if (is_array($value['values'])) {
				$x = $value['values'][0];
			} else {
				$x = $value['values'];
			}
		}
		if ($value['html'] != 'bool') {
			$x = "'$x'";
		}
		$config_file .= "define('".$value['name']."',$x,true);\n";
	}
	file_put_contents('inc/instance-config.php',$config_file);
	
	require 'inc/functions.php';
	require 'inc/display.php';
	require 'inc/instance-config.php';
	require 'inc/config.php';
	require 'inc/template.php';
	require 'inc/database.php';
	
	sql_open();
	foreach ($sql as $query) {
		query($query);
	}
	
	echo "<h1>Install Complete.</h1>";
	die();
}

echo "<form action=\"install.php\" method=\"POST\">";
foreach ($default_values as $value) {
	if (isset($value['output'])) {
		echo $value['output'];
		continue;
	}
	if (isset($value['error'])) {
		echo "<p>Error: ".htmlclean($value['error'])."</p>\n";
	}
	echo "<p>".$value['comment']." ";
	switch ($value['html']) {
		case 'text':
			echo "<input type=\"text\" id=\"".htmlclean($value['name'])."\" name=\"".htmlclean($value['name'])."\" value=\"".htmlclean($value['values'])."\" />";
			break;
		case 'password':
			echo "<input type=\"password\" id=\"".htmlclean($value['name'])."\" name=\"".htmlclean($value['name'])."\" value=\"".htmlclean($value['values'])."\" />";
			break;
		case 'dropdown':
			echo "<select name=\"".htmlclean($value['name'])."\" id=\"".htmlclean($value['name'])."\">\n";
			foreach ($value['values'] as $option => $human) {
				echo "<option value=\"".htmlclean($option)."\">".htmlclean($human)."</option>";
			}
			echo "</select>\n";
			break;
		case 'bool':
			if ($value['values']) {
				echo "<input type=\"radio\" id=\"".htmlclean($value['name'])."\" value=\"true\" name=\"".htmlclean($value['name'])."\" checked />True<br />";
				echo "<input type=\"radio\" id=\"".htmlclean($value['name'])."\" value=\"false\" name=\"".htmlclean($value['name'])."\" />False<br />";
			} else {
				echo "<input type=\"radio\" id=\"".htmlclean($value['name'])."\" value=\"true\" name=\"".htmlclean($value['name'])."\" />True<br />";
				echo "<input type=\"radio\" id=\"".htmlclean($value['name'])."\" value=\"false\" name=\"".htmlclean($value['name'])."\" checked />False<br />";
			}
			break;
		default:
			die('Internal Error. You have found a bug.');
	}
	echo "</p>\n";
}
echo "<p><input type=\"submit\" name=\"submit\" value=\"Install\" /></p>\n";
echo "</form>";

?>
