<?php
	if($_SERVER['SCRIPT_FILENAME'] == str_replace('\\', '/', __FILE__)) {
		// You cannot request this file directly.
		header('Location: ../', true, 302);
		exit;
	}
	
	// create a hash/salt pair for validate logins
	function mkhash($username, $password, $salt = false) {
		global $config;
		
		if(!$salt) {
			// create some sort of salt for the hash
			$salt = substr(base64_encode(sha1(rand() . time(), true) . $config['cookies']['salt']), 0, 15);
			
			$generated_salt = true;
		}
		
		// generate hash (method is not important as long as it's strong)
		$hash = substr(base64_encode(md5($username . sha1($username . $password . $salt . ($config['mod']['lock_ip'] ? $_SERVER['REMOTE_ADDR'] : ''), true), true)), 0, 20);
		
		if(isset($generated_salt))
			return Array($hash, $salt);
		else
			return $hash;
	}
	
	function hasPermission($action = null, $board = null, $_mod = null) {
		global $config;
		
		if(isset($_mod))
			$mod = &$_mod;
		else
			global $mod;
		
		if(isset($action) && $mod['type'] < $action)
			return false;
		
		if(!isset($board))
			return true;
		
		if(!$config['mod']['skip_per_board'] && !in_array('*', $mod['boards']) && !in_array($board, $mod['boards']))
			return false;
		
		return true;
	}
	
	function login($username, $password, $makehash=true) {
		global $mod;
		
		// SHA1 password
		if($makehash) {
			$password = sha1($password);
		}
		
		$query = prepare("SELECT `id`,`type`,`boards` FROM `mods` WHERE `username` = :username AND `password` = :password LIMIT 1");
		$query->bindValue(':username', $username);
		$query->bindValue(':password', $password);
		$query->execute() or error(db_error($query));
		
		if($user = $query->fetch()) {
			return $mod = Array(
				'id' => $user['id'],
				'type' => $user['type'],
				'username' => $username,
				'hash' => mkhash($username, $password),
				'boards' => explode(',', $user['boards'])
				);
		} else return false;
	}
	
	function setCookies() {
		global $mod, $config;
		if(!$mod)
			error('setCookies() was called for a non-moderator!');
		
		setcookie($config['cookies']['mod'],
				$mod['username'] . // username
				':' . 
				$mod['hash'][0] . // password
				':' .
				$mod['hash'][1], // salt
			time() + $config['cookies']['expire'], $config['cookies']['jail'] ? $config['cookies']['path'] : '/', null, false, true);
	}
	
	function destroyCookies() {
		global $config;
		// Delete the cookies
		setcookie($config['cookies']['mod'], 'deleted', time() - $config['cookies']['expire'], $config['cookies']['jail']?$config['cookies']['path'] : '/', null, false, true);
	}
	
	function create_pm_header() {
		global $mod;
		$query = prepare("SELECT `id` FROM `pms` WHERE `to` = :id AND `unread` = 1");
		$query->bindValue(':id', $mod['id'], PDO::PARAM_INT);
		$query->execute() or error(db_error($query));
		
		if($pm = $query->fetch()) {
			return Array('id' => $pm['id'], 'waiting' => $query->rowCount() - 1);
		}
		
		return false;
	}
	
	function modLog($action, $_board=null) {
		global $mod, $board;
		$query = prepare("INSERT INTO `modlogs` VALUES (:id, :ip, :board, :time, :text)");
		$query->bindValue(':id', $mod['id'], PDO::PARAM_INT);
		$query->bindValue(':ip', $_SERVER['REMOTE_ADDR']);
		$query->bindValue(':time', time(), PDO::PARAM_INT);
		$query->bindValue(':text', $action);
		if(isset($_board))
			$query->bindValue(':board', $_board);
		elseif(isset($board))
			$query->bindValue(':board', $board['id']);
		else
			$query->bindValue(':board', null, PDO::PARAM_NULL);
		$query->execute() or error(db_error($query));
		
		if($config['syslog'])
			_syslog(LOG_INFO, '[mod/' . $mod['username'] . ']: ' . $action);
	}
	
	// Generates a <ul> element with a list of linked
	// boards and their subtitles. (without the <ul> opening and ending tags)
	function ulBoards() {
		global $mod, $config;
		
		$body = '';
		
		// List of boards
		$boards = listBoards();
		
		foreach($boards as &$b) {
			$body .= '<li>' . 
				'<a href="?/' .
						sprintf($config['board_path'], $b['uri']) . $config['file_index'] .
						'">' .
					sprintf($config['board_abbreviation'], $b['uri']) .
					'</a> - ' .
					$b['title'] .
					(isset($b['subtitle']) ? '<span class="unimportant"> — ' . $b['subtitle'] . '</span>' : '') . 
						($mod['type'] >= $config['mod']['manageboards'] ?
							' <a href="?/board/' . $b['uri'] . '" class="unimportant">[manage]</a>' : '') .
				'</li>';
		}
		
		if($mod['type'] >= $config['mod']['newboard']) {
			$body .= '<li style="margin-top:15px;"><a href="?/new"><strong>' . _('Create new board') . '</strong></a></li>';
		}
		return $body;
	}
	
	function form_newBan($ip=null, $reason='', $continue=false, $delete=false, $board=false, $allow_public = false) {
		global $config, $mod;
		
		$boards = listBoards();
		$__boards = '<li><input type="radio" checked="checked" name="board_id" id="board_*" value="-1"/> <label style="display:inline" for="board_*"><em>' . _('all boards') . '</em></label></li>';
		foreach($boards as &$_board) {
			$__boards .= '<li>' .
						'<input type="radio" name="board_id" id="board_' . $_board['uri'] . '" value="' . $_board['id'] . '">' .
						'<label style="display:inline" for="board_' . $_board['uri'] . '"> ' .
							($_board['uri'] == '*' ?
								'<em>"*"</em>'
							:
								sprintf($config['board_abbreviation'], $_board['uri'])
							) .
							' - ' . $_board['title'] .
						'</label>' .
						'</li>';
		}
		
		return '<fieldset><legend>New ban</legend>' . 
					'<form action="?/ban" method="post">' . 
						($continue ? '<input type="hidden" name="continue" value="' . htmlentities($continue) . '" />' : '') .
						($delete || $allow_public ? '<input type="hidden" name="' . (!$allow_public ? 'delete' : 'post') . '" value="' . htmlentities($delete) . '" />' : '') .
						($board ? '<input type="hidden" name="board" value="' . htmlentities($board) . '" />' : '') .
						'<table>' .
						'<tr>' . 
							'<th><label for="ip">IP ' .
								($config['ban_cidr'] ? '<span class="unimportant">(or subnet)' : '') .
							'</span></label></th>' .
							'<td><input type="text" name="ip" id="ip" size="30" maxlength="30" ' . 
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
						($mod['type'] >= $config['mod']['public_ban'] && $allow_public ?
							'<tr>' . 
								'<th><label for="message">Message</label></th>' .
								'<td><input type="checkbox" id="public_message" name="public_message"/>' .
								' <input type="text" name="message" id="message" size="35" maxlength="200" value="' . htmlentities($config['mod']['default_ban_message']) . '" />' .
								' <span class="unimportant">(public; attached to post)</span></td>' .
									'<script type="text/javascript">' . 
										'document.getElementById(\'message\').disabled = true;' .
										'document.getElementById(\'public_message\').onchange = function() {' . 
											'document.getElementById(\'message\').disabled = !this.checked;' .
										'}' . 
										
									'</script>' .
							'</tr>'
						: '') .
						'<tr>' . 
							'<th><label for="length">Length</label></th>' .
							'<td><input type="text" name="length" id="length" size="20" maxlength="40" />' .
							' <span class="unimportant">(eg. "2d1h30m" or "2 days")</span></td>' .
						'</tr>' . 
						
						'<tr>' . 
							'<th>Board</th>' .
							'<td><ul style="list-style:none;padding:2px 5px">' . $__boards . '</tl></td>' .
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
	
	
	function removeBan($id) {
		global $config, $memcached;
		
		$query = prepare("DELETE FROM `bans` WHERE `id` = :id");
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		$query->execute() or error(db_error($query));
		
		//if($config['memcached']['enabled']) {
			// Remove cached ban
			// TODO
		//	$memcached->delete("ban_{$id}");
		//}
	}
	
	
	// Validate session
	
	if(isset($_COOKIE[$config['cookies']['mod']])) {
		// Should be username:hash:salt
		$cookie = explode(':', $_COOKIE[$config['cookies']['mod']]);
		if(count($cookie) != 3) {
			destroyCookies();
			error($config['error']['malformed']);
		}
		
		$query = prepare("SELECT `id`, `type`, `boards`, `password` FROM `mods` WHERE `username` = :username LIMIT 1");
		$query->bindValue(':username', $cookie[0]);
		$query->execute() or error(db_error($query));
		$user = $query->fetch();
		
		// validate password hash
		if($cookie[1] != mkhash($cookie[0], $user['password'], $cookie[2])) {
			// Malformed cookies
			destroyCookies();
			error($config['error']['malformed']);
		}
		
		$mod = Array(
			'id' => $user['id'],
			'type' => $user['type'],
			'username' => $cookie[0],
			'boards' => explode(',', $user['boards'])
		);
	}

