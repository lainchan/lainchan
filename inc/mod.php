<?php
		
	// Creates a small random string for validating moderators' cookies
	function mkhash($length=12) {
		// The method here isn't really important,
		// but I think this generates a relatively
		// unique string that looks cool.
		// If you choose to change this, make sure it cannot include a ':' character.
		return substr(base64_encode(sha1(rand() . time(), true)), 0, $length);
	}
	
	function login($username, $password, $makehash=true) {
		global $sql, $mod;
		
		// SHA1 password
		if($makehash) {
			$password = sha1($password);
		}
		
		$query = prepare("SELECT `id`,`type` FROM `mods` WHERE `username` = :username AND `password` = :password LIMIT 1");
		$query->bindValue(':username', $username);
		$query->bindValue(':password', $password);
		$query->execute();
		
		if($user = $query->fetch()) {
			return $mod = Array(
				'id' => $user['id'],
				'type' => $user['type'],
				'username' => $username,
				'password' => $password,
				'hash' => isset($_SESSION['mod']['hash']) ? $_SESSION['mod']['hash'] : mkhash()
				);
		} else return false;
	}
	
	function setCookies() {
		global $mod;
		if(!$mod) error('setCookies() was called for a non-moderator!');
		
		// MOD_COOKIE contains username:hash
		setcookie(MOD_COOKIE, $mod['username'] . ':' . $mod['hash'], time()+COOKIE_EXPIRE, JAIL_COOKIES?ROOT:'/', null, false, true);
		
		// Put $mod in the session
		$_SESSION['mod'] = $mod;
		
		// Lock sessions to IP addresses
		if(MOD_LOCK_IP)
			$_SESSION['mod']['ip'] = $_SERVER['REMOTE_ADDR'];
	}
	
	function destroyCookies() {
		// Delete the cookies
		setcookie(MOD_COOKIE, 'deleted', time()-COOKIE_EXPIRE, JAIL_COOKIES?ROOT:'/', null, false, true);
		
		// Unset the session
		unset($_SESSION['mod']);
	}
	
	if(isset($_COOKIE['mod']) && isset($_SESSION['mod']) && is_array($_SESSION['mod'])) {
		// Should be username:session hash
		$cookie = explode(':', $_COOKIE['mod']);
		if(count($cookie) != 2) {
			destroyCookies();
			error(ERROR_MALFORMED);
		}
		
		// Validate session
		if(	$cookie[0] != $_SESSION['mod']['username'] ||
			$cookie[1] != $_SESSION['mod']['hash']) {
			// Malformed cookies
			destroyCookies();
			error(ERROR_MALFORMED);
		}
		
		// Open connection
		sql_open();
		
		// Check username/password
		if(!login($_SESSION['mod']['username'], $_SESSION['mod']['password'], false)) {
			destroyCookies();
			error(ERROR_INVALIDAFTER);
		}
		
	}
	
	// Generates a <ul> element with a list of linked
	// boards and their subtitles. (without the <ul> opening and ending tags)
	function ulBoards() {
		global $mod;
		
		$body = '';
		
		// List of boards
		$boards = listBoards();
		
		foreach($boards as &$b) {
			$body .= '<li>' . 
				'<a href="?/' .
						sprintf(BOARD_PATH, $b['uri']) . FILE_INDEX .
						'">' .
					sprintf(BOARD_ABBREVIATION, $b['uri']) .
					'</a> - ' .
					$b['title'] .
					(isset($b['subtitle']) ? '<span class="unimportant"> — ' . $b['subtitle'] . '</span>' : '') . 
				'</li>';
		}
		
		if($mod['type'] >= MOD_NEWBOARD) {
			$body .= '<li style="margin-top:15px;"><a href="?/new"><strong>Create new board</strong></a></li>';
		}
		return $body;
	}
	
	function form_newBan($ip=null, $reason='', $continue=false) {
		return '<fieldset><legend>New ban</legend>' . 
					'<form action="" method="post">' . 
						($continue ? '<input type="hidden" name="continue" value="' . htmlentities($continue) . '" />' : '') .
						'<table>' .
						'<tr>' . 
							'<th><label for="ip">IP</label></th>' .
							'<td><input type="text" name="ip" id="ip" size="15" maxlength="15" ' . 
								(isset($ip) ?
									'value="' . htmlentities($ip) . '" ' : ''
								) .
							'/></td>' .
						'</tr>' . 
						'<tr>' . 
							'<th><label for="reason">Reason</label></th>' .
							'<td><textarea name="reason" id="reason" rows="5" cols="30">' .
								htmlentities($reason) .
							'</textarea></td>' .
						'</tr>' . 
						'<tr>' . 
							'<th><label for="length">Length</label></th>' .
							'<td><input type="text" name="length" id="length" size="20" maxlength="40" />' .
							' <span class="unimportant">(eg. "2d1h30m" or "2 days")</span></td>' .
						'</tr>' . 
						'<tr>' . 
							'<td></td>' . 
							'<td><input name="new_ban" type="submit" value="New Ban" /></td>' . 
						'</tr>' . 
						'</table>' .
					'</form>' .
				'</fieldset>';
	}
	
	function form_newBoard() {
		return '<fieldset><legend>New board</legend>' . 
					'<form action="?/new" method="post">' . 
						'<table>' .
						'<tr>' . 
							'<th><label for="board">URI</label></th>' .
							'<td><input type="text" name="uri" id="board" size="3" maxlength="8" />' .
							' <span class="unimportant">(eg. "b"; "mu")</span></td>' .
						'</tr>' . 
						'<tr>' . 
							'<th><label for="title">Title</label></th>' .
							'<td><input type="text" name="title" id="title" size="15" maxlength="20" />' .
							' <span class="unimportant">(eg. "Random")</span></td>' .
						'</tr>' . 
						'<tr>' . 
							'<th><label for="subtitle">Subtitle</label></th>' .
							'<td><input type="text" name="subtitle" id="subtitle" size="20" maxlength="40" />' .
							' <span class="unimportant">(optional)</span></td>' .
						'</tr>' . 
						'<tr>' . 
							'<td></td>' . 
							'<td><input name="new_board" type="submit" value="New Board" /></td>' . 
						'</tr>' . 
						'</table>' .
					'</form>' .
				'</fieldset>';
	}
	
	// Remove file from post
	function deleteFile($id) {
		global $board;
		
		$query = prepare(sprintf("SELECT `thread`,`thumb`,`file` FROM `posts_%s` WHERE `id` = :id AND `thread` IS NOT NULL LIMIT 1", $board['uri']));
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		$query->execute() or error(db_error($query));
		
		if($query->rowCount() < 1) {
			error(ERROR_INVALIDPOST);
		}
		
		$post = $query->fetch();
		
		$query = prepare(sprintf("UPDATE `posts_%s` SET `thumb` = NULL, `thumbwidth` = NULL, `thumbheight` = NULL, `filewidth` = NULL, `fileheight` = NULL, `filesize` = NULL, `filename` = NULL, `filehash` = NULL, `file` = :file WHERE `id` = :id OR `thread` = :id", $board['uri']));
		if($post['file'] == 'deleted') {
			// Already deleted; remove file fully
			$query->bindValue(':file', null, PDO::PARAM_NULL);
		} else {
			// Delete thumbnail
			@unlink($board['dir'] . DIR_THUMB . $post['thumb']);
			
			// Delete file
			@unlink($board['dir'] . DIR_IMG . $post['file']);
			
			// Set file to 'deleted'
			$query->bindValue(':file', 'deleted', PDO::PARAM_INT);
		}
		// Update database
		
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		$query->execute() or error(db_error($query));
		
		buildThread($post['thread']);
	}
	
	// Delete a post (reply or thread)
	function deletePost($id) {
		global $board;
		
		// Select post and replies (if thread) in one query
		$query = prepare(sprintf("SELECT `id`,`thread`,`thumb`,`file` FROM `posts_%s` WHERE `id` = :id OR `thread` = :id", $board['uri']));
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		$query->execute() or error(db_error($query));
		
		if($query->rowCount() < 1) {
			error(ERROR_INVALIDPOST);
		}
		
		// Delete posts and maybe replies
		while($post = $query->fetch()) {
			if(!$post['thread']) {
				// Delete thread HTML page
				@unlink($board['dir'] . DIR_RES . sprintf(FILE_PAGE, $post['id']));
			} elseif($query->rowCount() == 1) {
				// Rebuild thread
				$rebuild = $post['thread'];
			}
			if($post['thumb']) {
				// Delete thumbnail
				@unlink($board['dir'] . DIR_THUMB . $post['thumb']);
			}
			if($post['file']) {
				// Delete file
				@unlink($board['dir'] . DIR_IMG . $post['file']);
			}
		}
		
		$query = prepare(sprintf("DELETE FROM `posts_%s` WHERE `id` = :id OR `thread` = :id", $board['uri']));
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		$query->execute() or error(db_error($query));
		
		if(isset($rebuild)) {
			buildThread($rebuild);
		}
	}
?>