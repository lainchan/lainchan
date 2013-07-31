<?php

	/*
	 * This is no longer supported with the latest Tinyboard version. Sorry for the inconvenience.
	*/


	set_time_limit(0);
	$kusabaxc = Array();
	
	
	/* Config */
	
	// Path to KusabaX configuration file (config.php)
	// Warning: This script will ignore anything past the first `require`. This is only a problem if you've extensively modified your config.php
	$kusabaxc['config'] = '';
	
	/* End config */
	
	
	require 'inc/functions.php';
	require 'inc/display.php';
	require 'inc/template.php';
	require 'inc/database.php';
	require 'inc/user.php';
	
	if(!isset($kusabaxc['config']) || empty($kusabaxc['config']))
		error('Did you forget to configure the script?');
	
	if(!file_exists($kusabaxc['config']) || !is_readable($kusabaxc['config']))
		error('Kusaba X config file doesn\'t exist or I can\'t read it.');
	
	$temp = tempnam($config['tmp'], 'kusabax');
	
	$raw_config = file_get_contents($kusabaxc['config']);
	
	// replace __FILE__ with the actual filename
	$raw_config = str_replace('__FILE__', '\'' . addslashes(realpath($kusabaxc['config'])) . '\'', $raw_config);
	
	// remove anything after the first `require`
	$raw_config = substr($raw_config, 0, strpos($raw_config, 'require KU_ROOTDIR'));
	
	file_put_contents($temp, $raw_config);
	
	// Load KusabaX config
	require $temp;
	
	unlink($temp);
	
	if(KU_DBTYPE != 'mysql' && KU_DBTYPE != 'mysqli')
		error('Database type <strong>' . KU_DBTYPE . '</strong> not supported!');
	
	$kusabaxc['db']['type']		= 'mysql';
	$kusabaxc['db']['server']	= KU_DBHOST;
	$kusabaxc['db']['user']		= KU_DBUSERNAME;
	$kusabaxc['db']['password']	= KU_DBPASSWORD;
	$kusabaxc['db']['database']	= KU_DBDATABASE;
	$kusabaxc['db']['dsn']		= '';
	$kusabaxc['db']['timeout']	= 5;
	$kusabaxc['db']['persistent']	= false;
	
	
	// KusabaX functions
	function md5_decrypt($enc_text, $password, $iv_len = 16) {
		$enc_text = base64_decode($enc_text);
		$n = strlen($enc_text);
		$i = $iv_len;
		$plain_text = '';
		$iv = substr($password ^ substr($enc_text, 0, $iv_len), 0, 512);
		while ($i < $n) {
			$block = substr($enc_text, $i, 16);
			$plain_text .= $block ^ pack('H*', md5($iv));
			$iv = substr($block . $iv, 0, 512) ^ $password;
			$i += 16;
		}
		return preg_replace('/\\x13\\x00*$/', '', $plain_text);
	}
	
	// KusabaX -> Tinyboard HTML
	function convert_markup($body) {
		global $config;
		$body = stripslashes($body);
		
		// >quotes
		$body = str_replace('"unkfunc"', '"quote"', $body);
		
		// >>cites
		$body = preg_replace('/<a href="[^"]+?\/(\w+)\/res\/(\d+).html#(\d+)" onclick="return highlight\(\'\d+\', true\);" class="[^"]+">/', '<a onclick="highlightReply(\'$3\');" href="' . $config['root'] . '$1/res/$2.html#$3">', $body);
		
		// Public bans
		$body = preg_replace('/<br \/><font color="#FF0000"><b>\((.+?)\)<\/b><\/font>/', '<span class="public_ban">($1)</span>', $body);
		
		return $body;
	}
	
	$step = isset($_GET['step']) ? round($_GET['step']) : 0;
	$page = Array(
		'config' => $config,
		'title' => 'KusabaX Database Migration',
		'body' => ''
	);
	
	$log = Array();
	
	// Trick Tinyboard into opening the KusabaX databse instead
	$__temp = $config['db'];
	$config['db'] = $kusabaxc['db'];
	
	sql_open();
	
	// Get databse link
	$kusabax = $pdo;
	// Clear
	unset($pdo);
	
	// Open Tinyboard database
	$config['db'] = $__temp;
	unset($__temp);
	
	$k_query = $kusabax->query('SELECT * FROM `' . KU_DBPREFIX . 'boards`');
	$boards = listBoards();
	
	// Copy boards table, briefly
	$kusabax_boards = Array();
	while($board = $k_query->fetch()) {
		// For later use...
		$kusabax_boards[(int)$board['id']] = $board['name'];
		
		$already_exists = false;
		foreach($boards as &$_board) {
			if($_board['uri'] == $board['name']) {
				// Board already exists in Tinyboard...
				$log[] = 'Board /' . $board['name'] . '/ already exists.';
				$already_exists = true;
				break;
			}
		}
		if($already_exists)
			continue;
		
		$log[] = 'Creating board: <strong>/' . $board['name'] . '/</strong>';
		
		// Go ahead and create this new board...
		$query = prepare('INSERT INTO `boards` VALUES (NULL, :uri, :title, :subtitle)');
		$query->bindValue(':uri', $board['name']);
		$query->bindValue(':title', $board['desc']);
		$query->bindValue(':subtitle', null, PDO::PARAM_NULL);
		$query->execute() or error(db_error($query));
		
		// Posting table
		query(Element('posts.sql', Array('board' => $board['name']))) or error(db_error());
		
		// Set up board (create directories, etc.) by opening it
		openBoard($board['name']);
	}
	
	$k_query = $kusabax->query('SELECT `' . KU_DBPREFIX . 'posts`.*, `' . KU_DBPREFIX . '`.`type` FROM `' . KU_DBPREFIX . 'posts` LEFT JOIN `' . KU_DBPREFIX . 'staff` ON `posterauthority` = `' . KU_DBPREFIX . 'staff`.`id` WHERE `IS_DELETED` = 0') or error(db_error($kusabax));
	while($post = $k_query->fetch(PDO::FETCH_ASSOC)) {
		if(!isset($kusabax_boards[(int)$post['boardid']])) {
			// Board doesn't exist...
			continue;
		}
		$board = $kusabax_boards[(int)$post['boardid']];
		
		$log[] = 'Replicating post <strong>' . $post['id'] . '</strong> on /' . $board . '/';
		
		$query = prepare(sprintf("INSERT INTO `posts_%s` VALUES
			(
				:id, :thread, :subject, :email, :name, :trip, :capcode, :body, NULL, :time, :time, :thumb, :thumbwidth, :thumbheight, :file, :width, :height, :filesize, :filename, :filehash, :password, :ip, :sticky, :locked, 0, :embed
			)", $board));
		
		// Post ID
		$query->bindValue(':id', $post['id'], PDO::PARAM_INT);
		
		// Thread (`parentid`)
		if($post['parentid'] == 0)
			$query->bindValue(':thread', null, PDO::PARAM_NULL);
		else
			$query->bindValue(':thread', (int)$post['parentid'], PDO::PARAM_INT);
		
		// Name
		if(empty($post['name']))
			$post['name'] = $config['anonymous'];
		$query->bindValue(':name', trim($post['name']), PDO::PARAM_STR);
		
		// Trip
		if(empty($post['tripcode']))
			$query->bindValue(':trip', null, PDO::PARAM_NULL);
		else
			$query->bindValue(':trip', $post['tripcode'], PDO::PARAM_STR);
		
		// Email
		$query->bindValue(':email', trim($post['email']), PDO::PARAM_STR);
		
		// Subject
		$query->bindValue(':subject', trim($post['subject']), PDO::PARAM_STR);
		
		// Body (`message`)
		$query->bindValue(':body', convert_markup($post['message']), PDO::PARAM_STR);
		
		$embed_code = false;
		
		// File
		if(empty($post['file']) || $post['file'] == 'removed') {
			if($post['file'] == 'removed')
				$query->bindValue(':file', 'deleted', PDO::PARAM_STR);
			else
				$query->bindValue(':file', null, PDO::PARAM_NULL);
			$query->bindValue(':width', null, PDO::PARAM_NULL);
			$query->bindValue(':height', null, PDO::PARAM_NULL);
			$query->bindValue(':filesize', null, PDO::PARAM_NULL);
			$query->bindValue(':filename', null, PDO::PARAM_NULL);
			$query->bindValue(':filehash', null, PDO::PARAM_NULL);
			$query->bindValue(':thumb', null, PDO::PARAM_NULL);
			$query->bindValue(':thumbwidth', null, PDO::PARAM_NULL);
			$query->bindValue(':thumbheight', null, PDO::PARAM_NULL);
		} elseif($post['file_size'] == 0 && empty($post['file_md5'])) {
			// embed
			$query->bindValue(':file', null, PDO::PARAM_NULL);
			$query->bindValue(':width', null, PDO::PARAM_NULL);
			$query->bindValue(':height', null, PDO::PARAM_NULL);
			$query->bindValue(':filesize', null, PDO::PARAM_NULL);
			$query->bindValue(':filename', null, PDO::PARAM_NULL);
			$query->bindValue(':filehash', null, PDO::PARAM_NULL);
			$query->bindValue(':thumb', null, PDO::PARAM_NULL);
			$query->bindValue(':thumbwidth', null, PDO::PARAM_NULL);
			$query->bindValue(':thumbheight', null, PDO::PARAM_NULL);
			
			if($post['file_type'] == 'you') {
				// youtube
				
				foreach($config['embedding'] as $embed) {
					if(strpos($embed[0], 'youtube\.com') !== false) {
						$embed_code = preg_replace($embed[0], $embed[1], 'http://youtube.com/watch?v=' . $post['file']);
						$embed_code = str_replace('%%tb_width%%', $config['embed_width'], $embed_code);
						$embed_code = str_replace('%%tb_height%%', $config['embed_height'], $embed_code);
						
						$query->bindValue(':embed', $embed_code, PDO::PARAM_STR);
					}
				}
			}
		} else {
			$query->bindValue(':file', $post['file'] . '.' . $post['file_type'], PDO::PARAM_STR);
			$query->bindValue(':width', $post['image_w'], PDO::PARAM_INT);
			$query->bindValue(':height', $post['image_h'], PDO::PARAM_INT);
			$query->bindValue(':filesize', $post['file_size'], PDO::PARAM_INT);
			$query->bindValue(':filename', $post['file_original'] . '.' . $post['file_type'], PDO::PARAM_STR);
			// They use MD5; we use SHA1 by default.
			$query->bindValue(':filehash', null, PDO::PARAM_NULL);
			
			$query->bindValue(':thumb', $post['file'] . '.' . $post['file_type'], PDO::PARAM_STR);
			$query->bindValue(':thumbwidth', $post['thumb_w'], PDO::PARAM_INT);
			$query->bindValue(':thumbheight', $post['thumb_h'], PDO::PARAM_INT);
			
			// Copy file
			$file_path = KU_BOARDSDIR . $board . '/src/' . $post['file'] . '.' . $post['file_type'];
			$thumb_path = KU_BOARDSDIR . $board . '/thumb/' . $post['file'] . 's.' . $post['file_type'];
			
			$to_file_path = sprintf($config['board_path'], $board) . $config['dir']['img'] . $post['file'] . '.' . $post['file_type'];
			$to_thumb_path = sprintf($config['board_path'], $board) . $config['dir']['thumb'] . $post['file'] . '.' . $post['file_type'];
			
			if(!file_exists($to_file_path)) {
				$log[] = 'Copying file: <strong>' . $file_path . '</strong>';
				if(!@copy($file_path, $to_file_path)) {
					$err = error_get_last();
					$log[] = 'Could not copy <strong>' . $file_path . '</strong>: ' . $err['message'];
				}
			}
			
			if(!file_exists($to_thumb_path)) {
				$log[] = 'Copying file: <strong>' . $thumb_path . '</strong>';
				if(!@copy($thumb_path, $to_thumb_path)) {
					$err = error_get_last();
					$log[] = 'Could not copy <strong>' . $thumb_path. '</strong>: ' . $err['message'];
				}
			}
		}
		
		if(!$embed_code)
			$query->bindValue(':embed', null, PDO::PARAM_NULL);
		
		// IP
		$ip = md5_decrypt($post['ip'], KU_RANDOMSEED);
		if(!preg_match('/^\d+\.\d+\.\d+\.\d+$/', $ip)) {
			// Invalid IP address. Wrong KU_RANDOMSEED?
			
			$log[] = 'Invalid IP address returned after decryption. Wrong KU_RANDOMSEED?';
			$ip = '0.0.0.0'; // just set it to something valid and continue
		}
		$query->bindValue(':ip', $ip, PDO::PARAM_STR);
		
		// Time (`timestamp`)
		$query->bindValue(':time', $post['timestamp'], PDO::PARAM_INT);
		
		// Bump (`bumped`)
		$query->bindValue(':bump', $post['bumped'], PDO::PARAM_INT);
		
		// Locked
		$query->bindValue(':locked', $post['locked'], PDO::PARAM_INT);
		
		// Sticky
		$query->bindValue(':sticky', $post['stickied'], PDO::PARAM_INT);
		
		// Impossible
		$query->bindValue(':password', null, PDO::PARAM_NULL);
		
		if($post['posterauthority']) {
			$query->bindValue(':capcode', $post['type'] == 1 ? 'Admin' : 'Mod', PDO::PARAM_STR);
		} else {
			$query->bindValue(':capcode', null, PDO::PARAM_NULL);
		}
		
		// Insert post
		$query->execute() or $log[] = 'Error: ' . db_error($query);
	}
	
	// News
	$k_query = $kusabax->query('SELECT * FROM `' . KU_DBPREFIX . 'front` WHERE `page` = 0');
	while($news = $k_query->fetch()) {
		// Check if already exists
		$query = prepare("SELECT 1 FROM `news` WHERE `body` = :body AND `time` = :time");
		$query->bindValue(':time', $news['timestamp'], PDO::PARAM_INT);
		$query->bindValue(':body', $news['message'], PDO::PARAM_STR);
		$query->execute() or error(db_error($query));
		if($query->fetch())
			continue;		
		
		$query = prepare("INSERT INTO `news` VALUES (NULL, :name, :time, :subject, :body)");
		$query->bindValue(':name', $news['poster'], PDO::PARAM_STR);
		$query->bindValue(':time', $news['timestamp'], PDO::PARAM_INT);
		$query->bindValue(':subject', $news['subject'], PDO::PARAM_STR);
		$query->bindValue(':body', $news['message'], PDO::PARAM_STR);
		$query->execute() or $log[] = 'Error: ' . db_error($query);
	}
	
	$page['body'] = '<div class="ban"><h2>Migrating&hellip;</h2><p>';
	foreach($log as &$l) {
		$page['body'] .= $l . '<br/>';
	}
	$page['body'] .= '</p></div>';
	
	echo Element('page.html', $page);


