<?php	
	require 'inc/functions.php';
	require 'inc/display.php';
	require 'inc/template.php';
	require 'inc/database.php';
	require 'inc/user.php';
	
	// Fix for magic quotes
	if (get_magic_quotes_gpc()) {
		function strip_array($var) {
			return is_array($var) ? array_map("strip_array", $var) : stripslashes($var);
		}
		
		$_SESSION = strip_array($_SESSION);
		$_GET = strip_array($_GET);
		$_POST = strip_array($_POST);
	}
	
	if(isset($_POST['delete'])) {
		// Delete
		
		if(	!isset($_POST['board']) ||
			!isset($_POST['password'])
			)
			error($config['error']['bot']);
		
		$password = &$_POST['password'];
		
		if(empty($password))
			error($config['error']['invalidpassword']);
		
		$delete = Array();
		foreach($_POST as $post => $value) {
			if(preg_match('/^delete_(\d+)$/', $post, $m)) {
				$delete[] = (int)$m[1];
			}
		}
		
		
		
		// Check if banned
		checkBan();
		
		checkDNSBL();
			
		// Check if board exists
		if(!openBoard($_POST['board']))
			error($config['error']['noboard']);
		
		if(empty($delete))
			error($config['error']['nodelete']);
			
		foreach($delete as &$id) {
			$query = prepare(sprintf("SELECT `time`,`password` FROM `posts_%s` WHERE `id` = :id", $board['uri']));
			$query->bindValue(':id', $id, PDO::PARAM_INT);
			$query->execute() or error(db_error($query));
			
			if($post = $query->fetch()) {
				if(!empty($password) && $post['password'] != $password)
					error($config['error']['invalidpassword']);
				
				if($post['time'] >= time() - $config['delete_time']) {
					error(sprintf($config['error']['delete_too_soon'], until($post['time'] + $config['delete_time'])));
				}
				
				if(isset($_POST['file'])) {
					// Delete just the file
					deleteFile($id);
				} else {
					// Delete entire post
					deletePost($id);
				}
			}
		}
		
		buildIndex();
		
		sql_close();
		
		$is_mod = isset($_POST['mod']) && $_POST['mod'];
		$root = $is_mod ? $config['root'] . $config['file_mod'] . '?/' : $config['root'];
		
		header('Location: ' . $root . $board['dir'] . $config['file_index'], true, $config['redirect_http']);
	
	} elseif(isset($_POST['report'])) {
		if(	!isset($_POST['board']) ||
			!isset($_POST['password']) ||
			!isset($_POST['reason'])
			)
			error($config['error']['bot']);
		
		$report = Array();
		foreach($_POST as $post => $value) {
			if(preg_match('/^delete_(\d+)$/', $post, $m)) {
				$report[] = (int)$m[1];
			}
		}
		
		
		
		// Check if banned
		checkBan();
		
		checkDNSBL();
			
		// Check if board exists
		if(!openBoard($_POST['board']))
			error($config['error']['noboard']);
		
		if(empty($report))
			error($config['error']['noreport']);
		
		if(count($report) > $config['report_limit'])
			error($config['error']['toomanyreports']);
		
		$reason = &$_POST['reason'];
		markup($reason);
		
		foreach($report as &$id) {
			$query = prepare(sprintf("SELECT 1 FROM `posts_%s` WHERE `id` = :id", $board['uri']));
			$query->bindValue(':id', $id, PDO::PARAM_INT);
			$query->execute() or error(db_error($query));
			
			if($post = $query->fetch()) {
				$query = prepare("INSERT INTO `reports` VALUES (NULL, :time, :ip, :board, :post, :reason)");
				$query->bindValue(':time', time(), PDO::PARAM_INT);
				$query->bindValue(':ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
				$query->bindValue(':board', $board['id'], PDO::PARAM_INT);
				$query->bindValue(':post', $id, PDO::PARAM_INT);
				$query->bindValue(':reason', $reason, PDO::PARAM_STR);
				$query->execute() or error(db_error($query));
			}
		}
		
		sql_close();
		
		$is_mod = isset($_POST['mod']) && $_POST['mod'];
		$root = $is_mod ? $config['root'] . $config['file_mod'] . '?/' : $config['root'];
		
		header('Location: ' . $root . $board['dir'] . $config['file_index'], true, $config['redirect_http']);
	} elseif(isset($_POST['post'])) {
		if(	!isset($_POST['name']) ||
			!isset($_POST['email']) ||
			!isset($_POST['subject']) ||
			!isset($_POST['body']) ||
			!isset($_POST['board']) ||
			!isset($_POST['password'])
			) error($config['error']['bot']);
		
		$post = Array('board' => $_POST['board']);
		
		if(isset($_POST['thread'])) {
			$OP = false;
			$post['thread'] = round($_POST['thread']);
		} else $OP = true;
		
		if(!(($OP && $_POST['post'] == $config['button_newtopic']) ||
		    (!$OP && $_POST['post'] == $config['button_reply'])))
			error($config['error']['bot']);
		
		// Check the referrer
		if($OP) {
			if(!isset($_SERVER['HTTP_REFERER']) || !preg_match($config['url_match'], $_SERVER['HTTP_REFERER'])) error($config['error']['bot']);
		}
		
		// TODO: Since we're now using static HTML files, we can't give them cookies on their first page view
		// Find another anti-spam method.
		
		/*
		// Check if he has a valid cookie.
		if(!$user['valid']) error($config['error']['bot']);
		
		// Check how long he has been here.
		if(time()-$user['appeared']<LURKTIME) error(ERROR_LURK);
		*/
		
		// Check if banned
		checkBan();
		
		checkDNSBL();
			
		// Check if board exists
		if(!openBoard($post['board']))
			error($config['error']['noboard']);
		
		if(checkSpam())
			error($config['error']['spam']);
		
		if($config['robot_enable'] && $config['robot_mute']) {
			checkMute();
		}
		
		//Check if thread exists
		if(!$OP && !threadExists($post['thread']))
			error($config['error']['nonexistant']);
		
		// Check for an embed field
		if($config['enable_embedding'] && isset($_POST['embed']) && !empty($_POST['embed'])) {
			// yep; validate it
			$value = &$_POST['embed'];
			foreach($config['embedding'] as &$embed) {
				if($html = preg_replace($embed[0], $embed[1], $value)) {
					if($html == $value) {
						// Nope.
						continue;
					}
					
					// Width and height
					$html = str_replace('%%tb_width%%', $config['embed_width'], $html);
					$html = str_replace('%%tb_height%%', $config['embed_height'], $html);
					
					// Validated. It works.
					$post['embed'] = $html;
					// This looks messy right now, I know. I'll work on a better alternative later.
					$post['no_longer_require_an_image_for_op'] = true;
					break;
				}
			}
			if(!isset($post['embed'])) {
				error($config['error']['invalid_embed']);
			}
		}
		
		// Check for a file
		if($OP && !isset($post['no_longer_require_an_image_for_op'])) {
			if(!isset($_FILES['file']['tmp_name']) || empty($_FILES['file']['tmp_name']))
				error($config['error']['noimage']);
		}
		
		$post['name'] = (!empty($_POST['name'])?$_POST['name']:$config['anonymous']);
		$post['subject'] = &$_POST['subject'];
		$post['email'] = utf8tohtml($_POST['email']);
		$post['body'] = &$_POST['body'];
		$post['password'] = &$_POST['password'];
		$post['has_file'] = !isset($post['embed']) && (($OP && !isset($post['no_longer_require_an_image_for_op'])) || (isset($_FILES['file']) && !empty($_FILES['file']['tmp_name'])));
		
		$post['mod'] = isset($_POST['mod']) && $_POST['mod'];
		if($post['has_file'])
			$post['filename'] = utf8tohtml(get_magic_quotes_gpc() ? stripslashes($_FILES['file']['name']) : $_FILES['file']['name']);
		
		if($config['force_body'] && empty($post['body']))
			error($config['error']['tooshort_body']);
		
		if($config['reject_blank'] && !empty($post['body'])) {
			$stripped_whitespace = preg_replace('/[\s]/u', '', $post['body']);
			if(empty($stripped_whitespace))
				error($config['error']['tooshort_body']);
		}
		
		if($post['mod']) {
			require 'inc/mod.php';
			if(!$mod) {
				// Liar. You're not a mod.
				error($config['error']['notamod']);
			}
			
			$post['sticky'] = $OP && isset($_POST['sticky']);
			$post['locked'] = $OP && isset($_POST['lock']);
			$post['raw'] = isset($_POST['raw']);
			
			if($post['sticky'] && $mod['type'] < $config['mod']['sticky']) error($config['error']['noaccess']);
			if($post['locked'] && $mod['type'] < $config['mod']['lock']) error($config['error']['noaccess']);
			if($post['raw'] && $mod['type'] < $config['mod']['rawhtml']) error($config['error']['noaccess']);
		}
		
		// Check if thread is locked
		// but allow mods to post
		if(!$OP && (!$mod || $mod['type'] < $config['mod']['postinlocked'])) {
			if(threadLocked($post['thread']))
				error($config['error']['locked']);
		}
		
		if($post['has_file']) {
			$size = $_FILES['file']['size'];
			if($size > $config['max_filesize'])
				error(sprintf3($config['error']['filesize'], array(
					'sz'=>commaize($size),
					'filesz'=>commaize($size),
					'maxsz'=>commaize($config['max_filesize']))));
		}
		
		if($mod && $mod['type'] >= MOD && preg_match('/^((.+) )?## (.+)$/', $post['name'], $match)) {
			if(($mod['type'] == MOD && $match[3] == 'Mod') || $mod['type'] >= ADMIN) {
				$post['capcode'] = utf8tohtml($match[3]);
				$post['name'] = !empty($match[2])?$match[2]:$config['anonymous'];
			}
		} else {
			$post['capcode'] = false;
		}
		
		$trip = generate_tripcode($post['name']);
		$post['name'] = &$trip[0];
		$post['trip'] = (isset($trip[1])?$trip[1]:'');
		
		if(strtolower($post['email']) == 'noko') {
			$noko = true;
			$post['email'] = '';
		} else $noko = false;
		
		if($post['has_file']) {
			$post['extension'] = strtolower(substr($post['filename'], strrpos($post['filename'], '.') + 1));
			$post['file_id'] = time() . rand(100, 999);
			$post['file'] = $board['dir'] . $config['dir']['img'] . $post['file_id'] . '.' . $post['extension'];
			$post['thumb'] = $board['dir'] . $config['dir']['thumb'] . $post['file_id'] . '.' . ($config['thumb_ext'] ? $config['thumb_ext'] : $post['extension']);
		}
		
		// Check string lengths
		if(strlen($post['name']) > 50) error(sprintf($config['error']['toolong'], 'name'));			
		if(strlen($post['email']) > 40) error(sprintf($config['error']['toolong'], 'email'));
		if(strlen($post['subject']) > 40) error(sprintf($config['error']['toolong'], 'subject'));
		if(!$mod && strlen($post['body']) > $config['max_body']) error($config['error']['toolong_body']);
		if(!(!$OP && $post['has_file']) && strlen($post['body']) < 1) error($config['error']['tooshort_body']);
		if(strlen($post['password']) > 20) error(sprintf($config['error']['toolong'], 'password'));
		
		wordfilters($post['body']);
		
		$post['body_nomarkup'] = $post['body'];
		
		if(!($mod && isset($post['raw']) && $post['raw']))
			markup($post['body']);
		
		// Check for a flood
		if(!($mod && $mod['type'] >= $config['mod']['flood']) && checkFlood($post)) {
			error($config['error']['flood']);
		}
		
		// Custom anti-spam filters
		if(isset($config['flood_filters'])) {
			foreach($config['flood_filters'] as &$filter) {
				unset($did_not_match);
				// Set up default stuff
				if(!isset($filter['action']))
					$filter['action'] = 'reject';
				if(!isset($filter['message']))
					$filter['message'] = 'Posting throttled by flood filter.';
				
				foreach($filter['condition'] as $condition=>$value) {
					if($condition == 'posts_in_past_x_minutes' && isset($value[0]) && isset($value[1])) {
						// Check if there's been X posts in the past X minutes (on this board)
						
						$query = prepare(sprintf("SELECT COUNT(*) AS `posts` FROM `posts_%s` WHERE `time` >= :time", $board['uri']));	
						$query->bindValue(':time', time() - ($value[1] * 60), PDO::PARAM_INT);
						$query->execute() or error(db_error($query));
						if(($count = $query->fetch()) && $count['posts'] >= $value[0]) {
							// Matched filter
							continue;
						}
					} elseif($condition == 'threads_with_no_replies_in_past_x_minutes' && isset($value[0]) && isset($value[1])) {
						// Check if there's been X new empty threads posted in the past X minutes (on this board)
						
						// Confusing query. I couldn't think of anything simpler...
						$query = prepare(sprintf("SELECT ((SELECT COUNT(*) FROM `posts_%s` WHERE `thread` IS NULL AND `time` >= :time) - COUNT(DISTINCT(`threads`.`id`))) AS `posts` FROM `posts_%s` AS `threads` INNER JOIN `posts_%s` AS `replies` ON `replies`.`thread` = `threads`.`id` WHERE `threads`.`thread` IS NULL AND `threads`.`time` >= :time", $board['uri'], $board['uri'], $board['uri']));	
						$query->bindValue(':time', time() - ($value[1] * 60), PDO::PARAM_INT);
						$query->execute() or error(db_error($query));
						if(($count = $query->fetch()) && $count['posts'] >= $value[0]) {
							// Matched filter
							continue;
						}
					} elseif($condition == 'name') {
						if(preg_match($value, $post['name']))
							continue;
					} elseif($condition == 'trip') {
						if(preg_match($value, $post['trip']))
							continue;
					} elseif($condition == 'email') {
						if(preg_match($value, $post['email']))
							continue;
					} elseif($condition == 'subject') {
						if(preg_match($value, $post['subject']))
							continue;
					} elseif($condition == 'body') {
						if(preg_match($value, $post['body_nomarkup']))
							continue;
					} elseif($condition == 'extension') {
						if($post['has_file'] && preg_match($value, $post['extension']))
							continue;
					} elseif($condition == 'filename') {
						if($post['has_file'] && preg_match($value, $post['filename']))
							continue;
					} elseif($condition == 'has_file') {
						if($value == $post['has_file'])
							continue;
					} elseif($condition == 'ip') {
						if(preg_match($value, $_SERVER['REMOTE_ADDR']))
							continue;
					} elseif($condition == 'OP') {
						// Am I OP?
						if($value == $OP)
							continue;
					} else {
						// Unknown block
						continue;
					}
					
					$did_not_match = true;
					break;
				}
			}
			if(!isset($did_not_match)) {
				// Matched filter!
				if(isset($filter) && $filter['action'] == 'reject') {
					error($filter['message']);
				}
			}
		}
		
		if($post['has_file']) {
			if(!in_array($post['extension'], $config['allowed_ext']) && !in_array($post['extension'], $config['allowed_ext_files']))
				error($config['error']['unknownext']);
			
			if(in_array($post['extension'], $config['allowed_ext_files']))
				$__file = true;
			
			// Just trim the filename if it's too long
			if(strlen($post['filename']) > 30) $post['filename'] = substr($post['filename'], 0, 27).'…';
			// Move the uploaded file
			if(!@move_uploaded_file($_FILES['file']['tmp_name'], $post['file'])) error($config['error']['nomove']);
			
			if(!isset($__file)) {
				$size = @getimagesize($post['file']);
				$post['width'] = &$size[0];
				$post['height'] = &$size[1];
				
				// Check if the image is valid
				if($post['width'] < 1 || $post['height'] < 1) {
					undoImage($post);
					error($config['error']['invalidimg']);
				}
				
				if($post['width'] > $config['max_width'] || $post['height'] > $config['max_height']) {
					undoImage($post);
					error($config['error']['maxsize']);
				}
				
				// Check IE MIME type detection XSS exploit
				$buffer = file_get_contents($post['file'], null, null, null, 255);
				if(preg_match($config['ie_mime_type_detection'], $buffer)) {
					undoImage($post);
					error($config['error']['mime_exploit']);
				}
				
				if($config['minimum_copy_resize'] && $post['width'] <= $config['thumb_width'] && $post['height'] <= $config['thumb_height'] && $post['extension'] == ($config['thumb_ext'] ? $config['thumb_ext'] : $post['extension'])) {
					// Copy, because there's nothing to resize
					copy($post['file'], $post['thumb']);
					
					$post['thumbwidth'] = $post['width'];
					$post['thumbheight'] = $post['height'];
				} else {
					$image = createimage($post['extension'], $post['file']);
					
					// Create a thumbnail
					$thumb = resize($image, $post['width'], $post['height'], $post['thumb'], $config['thumb_width'], $config['thumb_height'], ($config['thumb_ext'] ? $config['thumb_ext'] : $post['extension']));
					
					$post['thumbwidth'] = $thumb['width'];
					$post['thumbheight'] = $thumb['height'];
				}
			} else {
				copy($config['file_thumb'], $post['thumb']);
				
				$size = @getimagesize($post['thumb']);
				$post['thumbwidth'] = $size[0];
				$post['thumbheight'] = $size[1];
			}
			
			$post['filehash'] = $config['file_hash']($post['file']);
			$post['filesize'] = filesize($post['file']);
		}
		
		if($post['has_file'] && $config['image_reject_repost'] && $p = getPostByHash($post['filehash'])) {
			undoImage($post);
			error(sprintf($config['error']['fileexists'], 
				$post['mod'] ? $config['root'] . $config['file_mod'] . '?/' : $config['root'] .
				$board['dir'] . $config['dir']['res'] .
					($p['thread'] ?
						$p['thread'] . '.html#' . $p['id']
					:
						$p['id'] . '.html'
					)
			));
		}
		
		if(!($mod && $mod['type'] >= $config['mod']['postunoriginal']) && $config['robot_enable'] && checkRobot($post['body_nomarkup'])) {
			undoImage($post);
			if($config['robot_mute']) {
				error(sprintf($config['error']['muted'], mute()));
			} else {
				error($config['error']['unoriginal']);
			}
		}
		
		// Remove DIR_* before inserting them into the database.
		if($post['has_file']) {
			$post['file'] = substr_replace($post['file'], '', 0, strlen($board['dir'] . $config['dir']['img']));
			$post['thumb'] = substr_replace($post['thumb'], '', 0, strlen($board['dir'] . $config['dir']['thumb']));
		}
		
		// Todo: Validate some more, remove messy code, allow more specific configuration
		$id = post($post, $OP);
		
		buildThread(($OP?$id:$post['thread']));
		
		if(!$OP && strtolower($post['email']) != 'sage' && ($config['reply_limit'] == 0 || numPosts($post['thread']) < $config['reply_limit'])) {
			bumpThread($post['thread']);
		}
		
		if($OP)
			clean();
		
		buildIndex();
		
		if(isset($_SERVER['HTTP_REFERER'])) {
			// Tell Javascript that we posted successfully
			if(isset($_COOKIE[$config['cookies']['js']]))
				$js = json_decode($_COOKIE[$config['cookies']['js']]);
			else
				$js = (object) Array();
			// Tell it to delete the cached post for referer
			$js->{$_SERVER['HTTP_REFERER']} = true;
			// Encode and set cookie
			setcookie($config['cookies']['js'], json_encode($js), 0, $config['cookies']['jail']?$config['cookies']['path']:'/', null, false, false);
		}
		
		$root = $post['mod'] ? $config['root'] . $config['file_mod'] . '?/' : $config['root'];
		
		if($config['always_noko'] || $noko) {
			$redirect = $root . $board['dir'] . $config['dir']['res'] . sprintf($config['file_page'], $OP?$id:$post['thread']) . (!$OP?'#'.$id:'');
		} else {
			$redirect = $root . $board['dir'] . $config['file_index'];
			
		}
		
		rebuildThemes('post');
		header('Location: ' . $redirect, true, $config['redirect_http']);
		
		sql_close();
		
		exit;
	} else {
		if(!file_exists($config['has_installed'])) {
			
			
			// Build all boards
			$boards = listBoards();
			foreach($boards as &$_board) {
				setupBoard($_board);
				buildIndex();
			}
			
			sql_close();
			touch($config['has_installed'], 0777);
			
			die(Element('page.html', Array(
				'index'=>ROOT,
				'title'=>'Success',
				'body'=>"<center>" .
						"<h2>Tinyboard is now installed!</h2>" .
						"</center>"
			)));
		} else {
			// They opened post.php in their browser manually.
			// Possible TODO: Redirect back to homepage.
			error($config['error']['nopost']);
		}
	}
?>
