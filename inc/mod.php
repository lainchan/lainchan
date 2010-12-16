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
		
		$res = mysql_query(sprintf(
			"SELECT `id`,`type` FROM `mods` WHERE `username` = '%s' AND `password` = '%s' LIMIT 1",
				mysql_real_escape_string($username),
				$password
		), $sql) or error(mysql_error($sql));
		
		if($user = mysql_fetch_array($res)) {
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
	
	function form_newBoard() {
		return '<fieldset><legend>New board</legend>' . 
					'<form action="?/new" method="post">' . 
						'<table>' .
						'<tr>' . 
							'<th><label for="board">URI:</label></th>' .
							'<td><input type="text" name="uri" id="board" size="3" maxlength="8" />' .
							' <span class="unimportant">(eg. "b"; "mu")</span>' .
						'</tr>' . 
						'<tr>' . 
							'<th><label for="title">Title:</label></th>' .
							'<td><input type="text" name="title" id="title" size="15" maxlength="20" />' .
							' <span class="unimportant">(eg. "Random")</span>' .
						'</tr>' . 
						'<tr>' . 
							'<th><label for="subtitle">Subtitle:</label></th>' .
							'<td><input type="text" name="subtitle" id="subtitle" size="20" maxlength="40" />' .
							' <span class="unimportant">(optional)</span>' .
						'</tr>' . 
						'<tr>' . 
							'<td></td>' . 
							'<td><input name="new_board" type="submit" value="New Board" /></td>' . 
						'</tr>' . 
						'</table>' .
					'</form>' .
				'</fieldset>';
	}
	
	// Delete a post (reply or thread)
	function deletePost($id) {
		global $board, $sql;
		
		// Select post and replies (if thread) in one query
		$post_res = mysql_query(sprintf(
				"SELECT `id`,`thread`,`thumb`,`file` FROM `posts_%s` WHERE `id` = '%d' OR `thread` = '%d'",
					mysql_real_escape_string($board['uri']),
					$id,
					$id
			), $sql) or error(mysql_error($sql));
		
		if(mysql_num_rows($post_res) < 1) {
			error(ERROR_INVALIDPOST);
		}
		
		// Delete posts and maybe replies
		while($post = mysql_fetch_array($post_res)) {
			if(!$post['thread']) {
				// Delete thread HTML page
				@unlink($board['dir'] . DIR_RES . sprintf(FILE_PAGE, $post['id']));
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
		
		mysql_query(sprintf(
			"DELETE FROM `posts_%s` WHERE `id` = '%d' OR `thread` = '%d'",
				mysql_real_escape_string($board['uri']),
				$id,
				$id
		), $sql) or error(mysql_error($sql));
	}
?>