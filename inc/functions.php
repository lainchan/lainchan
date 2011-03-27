<?php
	loadConfig();
	
	function loadConfig() {
		global $board, $config, $__ip;
		
		require 'config.php';
		if (file_exists('inc/instance-config.php')) {
			require 'instance-config.php';
		}
		if(isset($board['dir']) && file_exists($board['dir'] . '/config.php')) {
			require $board['dir'] . '/config.php';
		}
		
		if(!isset($config['url_stylesheet']))
			$config['url_stylesheet'] = $config['root'] . 'style.css';
		if(!isset($config['url_javascript']))
			$config['url_javascript'] = $config['root'] . 'main.js';
		
		if(!isset($config['post_url']))
			$config['post_url'] = $config['root'] . $config['file_post'];
		
		if(!isset($config['url_match']))
			$config['url_match'] = '/^' .
				(preg_match($config['url_regex'], $config['root']) ? '' :
					(@$_SERVER['HTTPS']?'https':'http') .
					':\/\/'.$_SERVER['HTTP_HOST']) .
					preg_quote($config['root'], '/') .
				'(' .
						str_replace('%s', '\w{1,8}', preg_quote($config['board_path'], '/')) .
					'|' .
						str_replace('%s', '\w{1,8}', preg_quote($config['board_path'], '/')) .
						preg_quote($config['file_index'], '/') .
					'|' .
						str_replace('%s', '\w{1,8}', preg_quote($config['board_path'], '/')) .
						str_replace('%d', '\d+', preg_quote($config['file_page'], '/')) .
					'|' .
						preg_quote($config['file_mod'], '/') .
					'\?\/.+' .
				')$/i';
		
		if(!isset($config['cookies']['path']))
			$config['cookies']['path'] = $config['root'];
			
		if(!isset($config['dir']['static']))
			$config['dir']['static'] = $config['root'] . 'static/';
		
		if(!isset($config['image_sticky']))
			$config['image_sticky'] = $config['dir']['static'] . 'sticky.gif';
		if(!isset($config['image_locked']))
			$config['image_locked'] = $config['dir']['static'] . 'locked.gif';
		if(!isset($config['image_deleted']))
			$config['image_deleted'] = $config['dir']['static'] . 'deleted.png';
		if(!isset($config['image_zip']))
			$config['image_zip'] = $config['dir']['static'] . 'zip.png';
		
		if(!isset($config['uri_thumb']))
			$config['uri_thumb'] = $config['root'] . $board['dir'] . $config['dir']['thumb'];
		else
			$config['uri_thumb'] = sprintf($config['uri_thumb'], $board['dir']);
			
		if(!isset($config['uri_img']))
			$config['uri_img'] = $config['root'] . $board['dir'] . $config['dir']['img'];
		else
			$config['uri_img'] = sprintf($config['uri_img'], $board['dir']);
		
		if(!isset($config['uri_stylesheets']))
			$config['uri_stylesheets'] = $config['root'];
		
		if($config['root_file']) {
			chdir($config['root_file']);
		}

		if($config['verbose_errors']) {
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
		}
		
		if($config['ipv6_ipv4'] && isset($_SERVER['REMOTE_ADDR'])) {
			// Keep the original address to properly comply with other board configurations
			if(!isset($__ip))
				$__ip = $_SERVER['REMOTE_ADDR'];
			
			// ::ffff:0.0.0.0
			if(preg_match('/^\:\:(ffff\:)?(\d+\.\d+\.\d+\.\d+)$/', $__ip, $m))
				$_SERVER['REMOTE_ADDR'] = $m[2];
		}
	}
	
	function sprintf3($str, $vars, $delim = '%') {
		$replaces = array();
		foreach($vars as $k => $v) {
			$replaces[$delim . $k . $delim] = $v;
		}
		return str_replace(array_keys($replaces),
		                   array_values($replaces), $str);
	}
	
	function setupBoard($array) {
		global $board, $config;
		
		$board = Array(
		'id' => $array['id'],
		'uri' => $array['uri'],
		'name' => $array['title'],
		'title' => $array['subtitle']);
		
		$board['dir'] = sprintf($config['board_path'], $board['uri']);
		$board['url'] = sprintf($config['board_abbreviation'], $board['uri']);
		
		loadConfig();
		
		if(!file_exists($board['dir'])) mkdir($board['dir'], 0777);
		if(!file_exists($board['dir'] . $config['dir']['img'])) @mkdir($board['dir'] . $config['dir']['img'], 0777) or error("Couldn't create " . $config['dir']['img'] . ". Check permissions.", true);
		if(!file_exists($board['dir'] . $config['dir']['thumb'])) @mkdir($board['dir'] . $config['dir']['thumb'], 0777) or error("Couldn't create " . $config['dir']['thumb'] . ". Check permissions.", true);
		if(!file_exists($board['dir'] . $config['dir']['res'])) @mkdir($board['dir'] . $config['dir']['res'], 0777) or error("Couldn't create " . $config['dir']['res'] . ". Check permissions.", true);
	}
	
	function openBoard($uri) {
		sql_open();
		
		$query = prepare("SELECT * FROM `boards` WHERE `uri` = :uri LIMIT 1");
		$query->bindValue(':uri', $uri);
		$query->execute() or error(db_error($query));
		
		if($board = $query->fetch()) {
			setupBoard($board);
			return true;
		} else return false;
	}
	
	function listBoards() {
		$query = query("SELECT * FROM `boards` ORDER BY `uri`") or error(db_error());
		$boards = $query->fetchAll();
		return $boards;
	}
	
	function checkFlood($post) {
		global $board, $config;
		
		$query = prepare(sprintf("SELECT * FROM `posts_%s` WHERE (`ip` = :ip AND `time` >= :floodtime) OR (`ip` = :ip AND `body` = :body AND `time` >= :floodsameiptime) OR (`body` = :body AND `time` >= :floodsametime) LIMIT 1", $board['uri']));
		$query->bindValue(':ip', $_SERVER['REMOTE_ADDR']);
		$query->bindValue(':body', $post['body'], PDO::PARAM_INT);
		$query->bindValue(':floodtime', time()-$config['flood_time'], PDO::PARAM_INT);
		$query->bindValue(':floodsameiptime', time()-$config['flood_time_ip'], PDO::PARAM_INT);
		$query->bindValue(':floodsametime', time()-$config['flood_time_same'], PDO::PARAM_INT);
		$query->execute() or error(db_error($query));
		
		return (bool)$query->fetch();
	}
	
	function until($timestamp) {
		$difference = $timestamp - time();
		if($difference < 60) {
			return $difference . ' second' . ($difference != 1 ? 's' : '');
		} elseif($difference < 60*60) {
			return ($num = round($difference/(60))) . ' minute' . ($num != 1 ? 's' : '');
		} elseif($difference < 60*60*24) {
			return ($num = round($difference/(60*60))) . ' hour' . ($num != 1 ? 's' : '');
		} elseif($difference < 60*60*24*7) {
			return ($num = round($difference/(60*60*24))) . ' day' . ($num != 1 ? 's' : '');
		} elseif($difference < 60*60*24*365) {
			return ($num = round($difference/(60*60*24*7))) . ' week' . ($num != 1 ? 's' : '');
		} else {
			return ($num = round($difference/(60*60*24*365))) . ' year' . ($num != 1 ? 's' : '');
		}
	}
	
	function ago($timestamp) {
		$difference = time() - $timestamp;
		if($difference < 60) {
			return $difference . ' second' . ($difference != 1 ? 's' : '');
		} elseif($difference < 60*60) {
			return ($num = round($difference/(60))) . ' minute' . ($num != 1 ? 's' : '');
		} elseif($difference < 60*60*24) {
			return ($num = round($difference/(60*60))) . ' hour' . ($num != 1 ? 's' : '');
		} elseif($difference < 60*60*24*7) {
			return ($num = round($difference/(60*60*24))) . ' day' . ($num != 1 ? 's' : '');
		} elseif($difference < 60*60*24*365) {
			return ($num = round($difference/(60*60*24*7))) . ' week' . ($num != 1 ? 's' : '');
		} else {
			return ($num = round($difference/(60*60*24*365))) . ' year' . ($num != 1 ? 's' : '');
		}
	}
	
	function formatDate($timestamp) {
		return date('jS F, Y', $timestamp);
	}
	
	function checkBan() {
		global $config;
		
		if(!isset($_SERVER['REMOTE_ADDR'])) {
			// Server misconfiguration
			return;
		}
		
		$query = prepare("SELECT * FROM `bans` WHERE `ip` = :ip LIMIT 1");
		$query->bindValue(':ip', $_SERVER['REMOTE_ADDR']);
		$query->execute() or error(db_error($query));
		
		if($ban = $query->fetch()) {
			if($ban['expires'] && $ban['expires'] < time()) {
				// Ban expired
				$query = prepare("DELETE FROM `bans` WHERE `ip` = :ip AND `expires` = :expires LIMIT 1");
				$query->bindValue(':ip', $_SERVER['REMOTE_ADDR']);
				$query->bindValue(':expires', $ban['expires'], PDO::PARAM_INT);
				$query->execute() or error(db_error($query));
				return;
			}
			$body = '<div class="ban">
		<h2>You are banned! ;_;</h2>
		<p>You have been banned ' .
			($ban['reason'] ? 'for the following reason:' : 'for an unspecified reason.') .
		'</p>' .
			($ban['reason'] ?
				'<p class="reason">' .
					$ban['reason'] .
				'</p>'
			: '') .
		'<p>Your ban was filed on <strong>' .
			formatDate($ban['set']) .
		'</strong>, and <span id="expires">' . 
			($ban['expires'] ?
				'expires <span id="countdown">' . until($ban['expires']) . '</span> from now, which is on <strong>' .
					formatDate($ban['expires']) .
				'</strong>
				<script>
					// return date("jS F, Y", $timestamp);
					var secondsLeft = ' . ($ban['expires'] - time()) . '
					var end = new Date().getTime() + secondsLeft*1000;
					function updateExpiresTime() {
						countdown.firstChild.nodeValue = until(end);
					}
					function until(end) {
						var now = new Date().getTime();
						var diff = Math.round((end - now) / 1000); // in seconds
						if (diff < 0) {
							document.getElementById("expires").innerHTML = "has since expired. Refresh the page to continue.";
							//location.reload(true);
							clearInterval(int);
							return "";
						} else if (diff < 60) {
							return diff + " second" + (diff == 1 ? "" : "s");
						} else if (diff < 60*60) {
							return (num = Math.round(diff/(60))) + " minute" + (num == 1 ? "" : "s");
						} else if (diff < 60*60*24) {
							return (num = Math.round(diff/(60*60))) + " hour" + (num == 1 ? "" : "s");
						} else if (diff < 60*60*24*7) {
							return (num = Math.round(diff/(60*60*24))) + " day" + (num == 1 ? "" : "s");
						} else if (diff < 60*60*24*365) {
							return (num = Math.round(diff/(60*60*24*7))) + " week" + (num == 1 ? "" : "s");
						} else {
							return (num = Math.round(diff/(60*60*24*365))) + " year" + (num == 1 ? "" : "s");
						}
					}
					var countdown = document.getElementById("countdown");
					
					updateExpiresTime();
					var int = setInterval(updateExpiresTime, 1000);
				</script>'
			: '<em>will not expire</em>.' ) .
		'</span></p>
		<p>Your IP address is <strong>' . $_SERVER['REMOTE_ADDR'] . '</strong>.</p>
	</div>';
			
			// Show banned page and exit
			die(Element('page.html', Array(
					'config' => $config,
					'title' => 'Banned',
					'subtitle' => 'You are banned!',
					'body' => $body
				)
			));
		}
	}
	
	function threadLocked($id) {
		global $board;
		
		$query = prepare(sprintf("SELECT `locked` FROM `posts_%s` WHERE `id` = :id AND `thread` IS NULL LIMIT 1", $board['uri']));
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		$query->execute() or error(db_error());
		
		if(!$post = $query->fetch()) {
			// Non-existant, so it can't be locked...
			return false;
		}
		
		return (bool) $post['locked'];
	}
	
	function threadExists($id) {
		global $board;
		
		$query = prepare(sprintf("SELECT 1 FROM `posts_%s` WHERE `id` = :id AND `thread` IS NULL LIMIT 1", $board['uri']));
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		$query->execute() or error(db_error());
		
		if($query->rowCount()) {
			return true;
		} else return false;
	}
	
	function post($post, $OP) {
		global $pdo, $board;
		
		$query = prepare(sprintf("INSERT INTO `posts_%s` VALUES ( NULL, :thread, :subject, :email, :name, :trip, :body, :time, :time, :thumb, :thumbwidth, :thumbheight, :file, :width, :height, :filesize, :filename, :filehash, :password, :ip, :sticky, :locked)", $board['uri']));
		
		// Basic stuff
		$query->bindValue(':subject', $post['subject']);
		$query->bindValue(':email', $post['email']);
		$query->bindValue(':name', $post['name']);
		$query->bindValue(':trip', $post['trip']);
		$query->bindValue(':body', $post['body']);
		$query->bindValue(':time', time(), PDO::PARAM_INT);
		$query->bindValue(':password', $post['password']);		
		$query->bindValue(':ip', $_SERVER['REMOTE_ADDR']);
		
		if($post['mod'] && $post['sticky']) {
			$query->bindValue(':sticky', 1, PDO::PARAM_INT);
		} else {
			$query->bindValue(':sticky', 0, PDO::PARAM_INT);
		}
		
		if($post['mod'] && $post['locked']) {
			$query->bindValue(':locked', 1, PDO::PARAM_INT);
		} else {
			$query->bindValue(':locked', 0, PDO::PARAM_INT);
		}
		
		if($OP) {
			// No parent thread, image
			$query->bindValue(':thread', null, PDO::PARAM_NULL);
		} else {
			$query->bindValue(':thread', $post['thread'], PDO::PARAM_INT);
		}
		
		if($post['has_file']) {
			$query->bindValue(':thumb', $post['thumb']);
			$query->bindValue(':thumbwidth', $post['thumbwidth'], PDO::PARAM_INT);
			$query->bindValue(':thumbheight', $post['thumbheight'], PDO::PARAM_INT);
			$query->bindValue(':file', $post['file']);
			$query->bindValue(':width', $post['width'], PDO::PARAM_INT);
			$query->bindValue(':height', $post['height'], PDO::PARAM_INT);
			$query->bindValue(':filesize', $post['filesize'], PDO::PARAM_INT);
			$query->bindValue(':filename', $post['filename']);
			$query->bindValue(':filehash', $post['filehash']);
		} else {
			$query->bindValue(':thumb', null, PDO::PARAM_NULL);
			$query->bindValue(':thumbwidth', null, PDO::PARAM_NULL);
			$query->bindValue(':thumbheight', null, PDO::PARAM_NULL);
			$query->bindValue(':file', null, PDO::PARAM_NULL);
			$query->bindValue(':width', null, PDO::PARAM_NULL);
			$query->bindValue(':height', null, PDO::PARAM_NULL);
			$query->bindValue(':filesize', null, PDO::PARAM_NULL);
			$query->bindValue(':filename', null, PDO::PARAM_NULL);
			$query->bindValue(':filehash', null, PDO::PARAM_NULL);
		}
		
		$query->execute() or error(db_error($query));
		
		return $pdo->lastInsertId();
	}
	
	function bumpThread($id) {
		global $board;
		$query = prepare(sprintf("UPDATE `posts_%s` SET `bump` = :time WHERE `id` = :id AND `thread` IS NULL", $board['uri']));
		$query->bindValue(':time', time(), PDO::PARAM_INT);
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		$query->execute() or error(db_error($query));
	}
	
	// Remove file from post
	function deleteFile($id, $remove_entirely_if_already=true) {
		global $board, $config;
		
		$query = prepare(sprintf("SELECT `thread`,`thumb`,`file` FROM `posts_%s` WHERE `id` = :id AND `thread` IS NOT NULL LIMIT 1", $board['uri']));
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		$query->execute() or error(db_error($query));
		
		if($query->rowCount() < 1) {
			error($config['error']['invalidpost']);
		}
		
		$post = $query->fetch();
		
		$query = prepare(sprintf("UPDATE `posts_%s` SET `thumb` = NULL, `thumbwidth` = NULL, `thumbheight` = NULL, `filewidth` = NULL, `fileheight` = NULL, `filesize` = NULL, `filename` = NULL, `filehash` = NULL, `file` = :file WHERE `id` = :id OR `thread` = :id", $board['uri']));
		if($post['file'] == 'deleted' && $remove_entirely_if_already) {
			// Already deleted; remove file fully
			$query->bindValue(':file', null, PDO::PARAM_NULL);
		} else {
			// Delete thumbnail
			@unlink($board['dir'] . $config['dir']['thumb'] . $post['thumb']);
			
			// Delete file
			@unlink($board['dir'] . $config['dir']['img'] . $post['file']);
			
			// Set file to 'deleted'
			$query->bindValue(':file', 'deleted', PDO::PARAM_INT);
		}
		// Update database
		
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		$query->execute() or error(db_error($query));
		
		buildThread($post['thread']);
	}
	
	// Delete a post (reply or thread)
	function deletePost($id, $error_if_doesnt_exist=true) {
		global $board, $config;
		
		// Select post and replies (if thread) in one query
		$query = prepare(sprintf("SELECT `id`,`thread`,`thumb`,`file` FROM `posts_%s` WHERE `id` = :id OR `thread` = :id", $board['uri']));
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		$query->execute() or error(db_error($query));
		
		if($query->rowCount() < 1) {
			if($error_if_doesnt_exist)
				error($config['error']['invalidpost']);
			else return false;
		}
		
		// Delete posts and maybe replies
		while($post = $query->fetch()) {
			if(!$post['thread']) {
				// Delete thread HTML page
				@unlink($board['dir'] . $config['dir']['res'] . sprintf($config['file_page'], $post['id']));
			} elseif($query->rowCount() == 1) {
				// Rebuild thread
				$rebuild = $post['thread'];
			}
			if($post['thumb']) {
				// Delete thumbnail
				@unlink($board['dir'] . $config['dir']['thumb'] . $post['thumb']);
			}
			if($post['file']) {
				// Delete file
				@unlink($board['dir'] . $config['dir']['img'] . $post['file']);
			}
		}
		
		$query = prepare(sprintf("DELETE FROM `posts_%s` WHERE `id` = :id OR `thread` = :id", $board['uri']));
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		$query->execute() or error(db_error($query));
		
		if(isset($rebuild)) {
			buildThread($rebuild);
		}
		
		return true;
	}
	
	function clean() {
		global $board, $config;
		$offset = round($config['max_pages']*$config['threads_per_page']);
		
		// I too wish there was an easier way of doing this...
		$query = prepare(sprintf("SELECT `id` FROM `posts_%s` WHERE `thread` IS NULL ORDER BY `sticky` DESC, `bump` DESC LIMIT :offset, 9001", $board['uri']));
		$query->bindValue(':offset', $offset, PDO::PARAM_INT);
		
		$query->execute() or error(db_error($query));
		while($post = $query->fetch()) {
			deletePost($post['id']);
		}
	}
	
	function index($page, $mod=false) {
		global $board, $config;

		$body = '';
		$offset = round($page*$config['threads_per_page']-$config['threads_per_page']);

		sql_open();
		
		$query = prepare(sprintf("SELECT * FROM `posts_%s` WHERE `thread` IS NULL ORDER BY `sticky` DESC, `bump` DESC LIMIT ?,?", $board['uri']));
		$query->bindValue(1, $offset, PDO::PARAM_INT);
		$query->bindValue(2, $config['threads_per_page'], PDO::PARAM_INT);
		$query->execute() or error(db_error($query));
		
		if($query->rowcount() < 1 && $page > 1) return false;
		while($th = $query->fetch()) {
			$thread = new Thread($th['id'], $th['subject'], $th['email'], $th['name'], $th['trip'], $th['body'], $th['time'], $th['thumb'], $th['thumbwidth'], $th['thumbheight'], $th['file'], $th['filewidth'], $th['fileheight'], $th['filesize'], $th['filename'], $th['ip'], $th['sticky'], $th['locked'], $mod ? '?/' : $config['root'], $mod);

			$posts = prepare(sprintf("SELECT `id`, `subject`, `email`, `name`, `trip`, `body`, `time`, `thumb`, `thumbwidth`, `thumbheight`, `file`, `filewidth`, `fileheight`, `filesize`, `filename`,`ip` FROM `posts_%s` WHERE `thread` = ? ORDER BY `id` DESC LIMIT ?", $board['uri']));
			$posts->bindValue(1, $th['id']);
			$posts->bindValue(2, ($th['sticky'] ? $config['threads_preview_sticky'] : $config['threads_preview']), PDO::PARAM_INT);
			$posts->execute() or error(db_error($posts));
			
			$num_images = 0;
			while($po = $posts->fetch()) {
				if($po['file'])
					$num_images++;
					
				$thread->add(new Post($po['id'], $th['id'], $po['subject'], $po['email'], $po['name'], $po['trip'], $po['body'], $po['time'], $po['thumb'], $po['thumbwidth'], $po['thumbheight'], $po['file'], $po['filewidth'], $po['fileheight'], $po['filesize'], $po['filename'], $po['ip'], $mod ? '?/' : $config['root'], $mod));
			}
			
			if($posts->rowCount() == ($th['sticky'] ? $config['threads_preview_sticky'] : $config['threads_preview'])) {
				$count = prepare(sprintf("SELECT COUNT(`id`) as `num` FROM `posts_%s` WHERE `thread` = :thread UNION ALL SELECT COUNT(`id`) FROM `posts_%s` WHERE `file` IS NOT NULL AND `thread` = :thread", $board['uri'], $board['uri']));
				$count->bindValue(':thread', $th['id'], PDO::PARAM_INT);
				$count->execute() or error(db_error($count));
				
				$c = $count->fetch();
				$thread->omitted = $c['num'] - ($th['sticky'] ? $config['threads_preview_sticky'] : $config['threads_preview']);
				
				$c = $count->fetch();
				$thread->omitted_images = $c['num'] - $num_images;
			}
			
			$thread->posts = array_reverse($thread->posts);
			$body .= $thread->build(true);
		}
		
		return Array(
			'board'=>$board,
			'body'=>$body,
			'post_url' => $config['post_url'],
			'config' => $config,
			'boardlist' => createBoardlist($mod)
		);
	}
	
	function getPageButtons($pages, $mod=false) {
		global $config, $board;
		
		$btn = Array();
		$root = ($mod ? '?/' : $config['root']) . $board['dir'];
		
		foreach($pages as $num => $page) {
			if(isset($page['selected'])) {
				// Previous button
				if($num == 0) {
					// There is no previous page.
					$btn['prev'] = 'Previous';
				} else {
					$loc = ($mod ? '?/' . $board['uri'] . '/' : '') .
						($num == 1 ?
							$config['file_index']
						:
							sprintf($config['file_page'], $num)
						);
					
					$btn['prev'] = '<form action="' . ($mod ? '' : $root . $loc) . '" method="get">' .
						($mod ?
							'<input type="hidden" name="status" value="301" />' .
							'<input type="hidden" name="r" value="' . htmlentities($loc) . '" />'
						:'') .
					'<input type="submit" value="Previous" /></form>';
				}
				
				if($num == count($pages) - 1) {
					// There is no next page.
					$btn['next'] = 'Next';
				} else {
					$loc = ($mod ? '?/' . $board['uri'] . '/' : '') . sprintf($config['file_page'], $num + 2);
					
					$btn['next'] = '<form action="' . ($mod ? '' : $root . $loc) . '" method="get">' .
						($mod ?
							'<input type="hidden" name="status" value="301" />' .
							'<input type="hidden" name="r" value="' . htmlentities($loc) . '" />'
						:'') .
					'<input type="submit" value="Next" /></form>';
				}
			}
		}
		
		return $btn;
	}
	
	function getPages($mod=false) {
		global $board, $config;
		
		// Count threads
		$query = query(sprintf("SELECT COUNT(`id`) as `num` FROM `posts_%s` WHERE `thread` IS NULL", $board['uri'])) or error(db_error());
		
		$count = current($query->fetch());
		$count = floor(($config['threads_per_page'] + $count - 1) / $config['threads_per_page']);
		
		if($count < 1) $count = 1;
		
		$pages = Array();
		for($x=0;$x<$count && $x<$config['max_pages'];$x++) {
			$pages[] = Array(
				'num' => $x+1,
				'link' => $x==0 ? ($mod ? '?/' : $config['root']) . $board['dir'] . $config['file_index'] : ($mod ? '?/' : $config['root']) . $board['dir'] . sprintf($config['file_page'], $x+1)
			);
		}
		
		return $pages;
	}
	
	function makerobot($body) {
		global $config;
		$body = strtolower($body);
		
		// Leave only letters
		$body = preg_replace('/[^a-z]/i', '', $body);
		// Remove repeating characters
		if($config['robot_strip_repeating'])
			$body = preg_replace('/(.)\\1+/', '$1', $body);
		
		return sha1($body);
	}
	
	function checkRobot($body) {
		/* CREATE TABLE `robot` (
`hash` VARCHAR( 40 ) NOT NULL COMMENT  'SHA1'
) ENGINE = INNODB; */
		/* CREATE TABLE `mutes` (
`ip` VARCHAR( 15 ) NOT NULL ,
`time` INT NOT NULL
) ENGINE = MYISAM ; */

		$body = makerobot($body);
		$query = prepare("SELECT 1 FROM `robot` WHERE `hash` = :hash LIMIT 1");
		$query->bindValue(':hash', $body);
		$query->execute() or error(db_error($query));

		if($query->fetch()) {
			return true;
		} else {
			// Insert new hash
			
			$query = prepare("INSERT INTO `robot` VALUES (:hash)");
			$query->bindValue(':hash', $body);
			$query->execute() or error(db_error($query));
			return false;
		}
	}
	
	function numPosts($id) {
		global $board;
		$query = prepare(sprintf("SELECT COUNT(*) as `count` FROM `posts_%s` WHERE `thread` = :thread", $board['uri']));
		$query->bindValue(':thread', $id, PDO::PARAM_INT);
		$query->execute() or error(db_error($query));
		
		$result = $query->fetch();
		return $result['count'];
	}
	
	function muteTime() {
		global $config;
		// Find number of mutes in the past X hours
		$query = prepare("SELECT COUNT(*) as `count` FROM `mutes` WHERE `time` >= :time AND `ip` = :ip");
		$query->bindValue(':time', time()-($config['robot_mute_hour']*3600), PDO::PARAM_INT);
		$query->bindValue(':ip', $_SERVER['REMOTE_ADDR']);
		$query->execute() or error(db_error($query));
		
		$result = $query->fetch();
		if($result['count'] == 0) return 0;
		return pow($config['robot_mute_multiplier'], $result['count']);
	}
	
	function mute() {
		// Insert mute
		$query = prepare("INSERT INTO `mutes` VALUES (:ip, :time)");
		$query->bindValue(':time', time(), PDO::PARAM_INT);
		$query->bindValue(':ip', $_SERVER['REMOTE_ADDR']);
		$query->execute() or error(db_error($query));
		
		return muteTime();
	}
	
	function checkMute() {
		global $config;
		
		$mutetime = muteTime();
		if($mutetime > 0) {
			// Find last mute time
			$query = prepare("SELECT `time` FROM `mutes` WHERE `ip` = :ip ORDER BY `time` DESC LIMIT 1");
			$query->bindValue(':ip', $_SERVER['REMOTE_ADDR']);
			$query->execute() or error(db_error($query));
			
			if(!$mute = $query->fetch()) {
				// What!? He's muted but he's not muted...
				return;
			}
			
			if($mute['time'] + $mutetime > time()) {
				// Not expired yet
				error(sprintf($config['error']['youaremuted'], $mute['time'] + $mutetime - time()));
			} else {
				// Already expired	
				return;
			}
		}
	}
	
	function createHiddenInputs() {
		global $config;
		
		$inputs = Array();
		
		shuffle($config['spam']['hidden_input_names']);
		$hidden_input_names_x = 0;
		
		$input_count = rand($config['spam']['hidden_inputs_min'], $config['spam']['hidden_inputs_max']);
		for($x=0;$x<$input_count;$x++) {
			if(rand(0, 2) == 0 || $hidden_input_names_x < 0) {
				// Use an obscure name
				$name = substr(base64_encode(sha1(rand())), 0, rand(2, 40));
			} else {
				// Use a pre-defined confusing name
				$name = $config['spam']['hidden_input_names'][$hidden_input_names_x++];
				if($hidden_input_names_x >= count($config['spam']['hidden_input_names']))
					$hidden_input_names_x = -1;
			}
			
			if(rand(0, 2) == 0) {
				// Value must be null
				$inputs[$name] = '';
			} elseif(rand(0, 4) == 0) {
				// Numeric value
				$inputs[$name] = rand(0, 100);
			} else {
				// Obscure value
				$inputs[$name] = substr(base64_encode(sha1(rand())), 0, rand(2, 40));
			}
		}
		
		$content = '';
		foreach($inputs as $name => $value) {
			$display_type = rand(0, 8);
			
			switch($display_type) {
				case 0:
					$content .= '<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />';
					break;
				case 1:
					$content .= '<input style="display:none" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />';
					break;
				case 2:
					$content .= '<input type="hidden" value="' . htmlspecialchars($value) . '" name="' . htmlspecialchars($name) . '" />';
					break;
				case 3:
					$content .= '<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />';
					break;
				case 4:
					$content .= '<span style="display:none"><input type="text" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" /></span>';
					break;
				case 5:
					$content .= '<div style="display:none"><input type="text" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" /></div>';
					break;
				case 6:
					$content .= '<textarea style="display:none" name="' . htmlspecialchars($name) . '">' . htmlspecialchars($value) . '</textarea>';
					break;
				case 7:
					$content .= '<textarea name="' . htmlspecialchars($name) . '" style="display:none">' . htmlspecialchars($value) . '</textarea>';
					break;
				case 8:
					$content .= '<div style="display:none"><textarea name="' . htmlspecialchars($name) . '" style="display:none">' . htmlspecialchars($value) . '</textarea></div>';
					break;
			}
		}
		
		// Create a hash to validate it after
		// This is the tricky part.
		
		// First, sort the keys in alphabetical order (A-Z)
		ksort($inputs);
		
		$hash = '';
		
		// Iterate through each input
		foreach($inputs as $name => $value) {
			$hash .= $name . '=' . $value;
		}
		
		// Add a salt to the hash
		$hash .= $config['cookies']['salt'];
		
		// Use SHA1 for the hash
		$hash = sha1($hash);
		
		// Append it to the HTML
		$content .= '<input type="hidden" name="hash" value="' . $hash . '" />';
		
		return $content;
	}
	
	function checkSpam() {
		global $config;
		
		if(!isset($_POST['hash']))
			return true;
		
		$hash = $_POST['hash'];
		
		// Reconsturct the $inputs array
		$inputs = Array();
		
		foreach($_POST as $name => $value) {
			if(in_array($name, $config['spam']['valid_inputs']))
				continue;
			
			$inputs[$name] = $value;
		}
		
		// Sort the inputs in alphabetical order (A-Z)
		ksort($inputs);
		
		$_hash = '';
		
		// Iterate through each input
		foreach($inputs as $name => $value) {
			$_hash .= $name . '=' . $value;
		}
		
		// Add a salt to the hash
		$_hash .= $config['cookies']['salt'];
		
		// Use SHA1 for the hash
		$_hash = sha1($_hash);
		
		return $hash != $_hash;
	}
	
	function buildIndex() {
		global $board, $config;
		sql_open();
		
		$pages = getPages();

		$page = 1;
		while($page <= $config['max_pages'] && $content = index($page)) {
			$filename = $board['dir'] . ($page==1 ? $config['file_index'] : sprintf($config['file_page'], $page));
			if(file_exists($filename)) $md5 = md5_file($filename);

			$content['pages'] = $pages;
			$content['pages'][$page-1]['selected'] = true;
			$content['btn'] = getPageButtons($content['pages']);
			$content['hidden_inputs'] = createHiddenInputs();
			@file_put_contents($filename, Element('index.html', $content)) or error("Couldn't write to file.");
			
			if(isset($md5) && $md5 == md5_file($filename)) {
				break;
			}
			$page++;
		}
		if($page < $config['max_pages']) {
			for(;$page<=$config['max_pages'];$page++) {
				$filename = $page==1 ? $config['file_index'] : sprintf($config['file_page'], $page);
				@unlink($filename);
			}
		}
	}
	
	function buildJavascript() {
		global $config;
		
		$stylesheets = Array();
		foreach($config['stylesheets'] as $name => $uri) {
			$stylesheets[] = Array(
				'name' => addslashes($name),
				'uri' => addslashes((!empty($uri) ? $config['uri_stylesheets'] : '') . $uri));
		}
		
		file_put_contents($config['file_script'], Element('main.js', Array(
			'config' => $config,
			'stylesheets' => $stylesheets
		)));
	}
	
	function isDNSBL() {
		$dns_black_lists = file('./dnsbl.txt', FILE_IGNORE_NEW_LINES);
		
		// Reverse the IP
		$rev_ip = implode(array_reverse(explode('.', $_SERVER['REMOTE_ADDR'])), '.');
		$response = array();
		foreach ($dns_black_lists as $dns_black_list) {
			$response = (gethostbynamel($rev_ip . '.' . $dns_black_list));
			if(!empty($response))
				return true;
		}
		
		return false;
	}
	
	function isIPv6() {
		return strstr($_SERVER['REMOTE_ADDR'], ':') !== false;
	}
	
	function isTor() {
		if(isIPv6())
			return false; // Tor does not support IPv6
		
		return gethostbyname(
				ReverseIPOctets($_SERVER['REMOTE_ADDR']) . '.' . $_SERVER['SERVER_PORT'] . '.' . ReverseIPOctets($_SERVER['SERVER_ADDR']) . '.ip-port.exitlist.torproject.org'
			) == '127.0.0.2';
	}
			
	function ReverseIPOctets($ip) {
		$ipoc = explode('.', $ip);
		return $ipoc[3] . '.' . $ipoc[2] . '.' . $ipoc[1] . '.' . $ipoc[0];
	}

	function markup(&$body) {
		global $board, $config;
		
		$body = utf8tohtml($body, true);
		
		if($config['wiki_markup']) {
			$body = preg_replace("/(^|\n)==(.+?)==\n?/m", "<span class=\"heading\">$2</span>", $body);
			$body = preg_replace("/'''(.+?)'''/m", "<strong>$1</strong>", $body);
			$body = preg_replace("/''(.+?)''/m", "<em>$1</em>", $body);
			$body = preg_replace("/\*\*(.+?)\*\*/m", "<span class=\"spoiler\">$1</span>", $body);
		}
		
		if($config['markup_urls']) {
			$body = preg_replace($config['url_regex'], "<a target=\"_blank\" rel=\"nofollow\" href=\"$0\">$0</a>", $body, -1, $num_links);
			if($num_links > $config['max_links'])
				error($config['error']['toomanylinks']);
		}
			
		if($config['auto_unicode']) {
			$body = str_replace('...', '…', $body);
			$body = str_replace('<--', '←', $body);
			$body = str_replace('-->', '→', $body);

			// En and em- dashes are rendered exactly the same in
			// most monospace fonts (they look the same in code
			// editors).
			$body = str_replace('---', '—', $body); // em dash
			$body = str_replace('--', '–', $body); // en dash
		}

		// Cites
		if(isset($board) && preg_match_all('/(^|\s)&gt;&gt;([0-9]+?)(\s|$)/', $body, $cites)) {
			$previousPosition = 0;
			$temp = '';
			sql_open();
			for($index=0;$index<count($cites[0]);$index++) {
				$cite = $cites[2][$index];
				$whitespace = Array(
					strlen($cites[1][$index]),
					strlen($cites[3][$index]),
				);
				$query = prepare(sprintf("SELECT `thread`,`id` FROM `posts_%s` WHERE `id` = :id LIMIT 1", $board['uri']));
				$query->bindValue(':id', $cite);
				$query->execute() or error(db_error($query));
				
				if($post = $query->fetch()) {
					$replacement = '<a onclick="highlightReply(\''.$cite.'\');" href="' . $config['root'] . $board['dir'] . $config['dir']['res'] . ($post['thread']?$post['thread']:$post['id']) . '.html#' . $cite . '">&gt;&gt;' . $cite . '</a>';
				} else {
					$replacement = "&gt;&gt;{$cite}";
				}

				// Find the position of the cite
				$position = strpos($body, $cites[0][$index]);
				
				
				
				// Replace the found string with "xxxx[...]". (allows duplicate tags). Keeps whitespace.
				$body = substr_replace($body, str_repeat('x', strlen($cites[0][$index]) - $whitespace[0] - $whitespace[1]), $position + $whitespace[0], strlen($cites[0][$index]) - $whitespace[0] - $whitespace[1]);
				
				$temp .= substr($body, $previousPosition, $position-$previousPosition) . $cites[1][$index] . $replacement . $cites[3][$index];
				$previousPosition = $position+strlen($cites[0][$index]);
			}
			
			// The rest
			$temp .= substr($body, $previousPosition);
				
			$body = $temp;
		}

		$body = str_replace("\r", '', $body);
		
		$body = preg_replace("/(^|\n)([\s]+)?(&gt;)([^\n]+)?($|\n)/m", '$1$2<span class="quote">$3$4</span>$5', $body);
		
		if($config['strip_superfluous_returns'])
			$body = preg_replace('/\s+$/', '', $body);
		
		$body = preg_replace("/\n/", '<br/>', $body);
	}

	function utf8tohtml($utf8, $encodeTags=true) {
		$result = '';
		for ($i = 0; $i < strlen($utf8); $i++) {
			$char = $utf8[$i];
			$ascii = ord($char);
			if ($ascii < 128) {
				// one-byte character
				$result .= ($encodeTags) ? htmlentities($char) : $char;
			} else if ($ascii < 192) {
				// non-utf8 character or not a start byte
			} else if ($ascii < 224) {
				// two-byte character
				$result .= htmlentities(substr($utf8, $i, 2), ENT_QUOTES, 'UTF-8');
				$i++;
			} else if ($ascii < 240) {
				// three-byte character
				$ascii1 = ord($utf8[$i+1]);
				$ascii2 = ord($utf8[$i+2]);
				$unicode = (15 & $ascii) * 4096 +
						   (63 & $ascii1) * 64 +
						   (63 & $ascii2);
				$result .= "&#$unicode;";
				$i += 2;
			} else if ($ascii < 248) {
				// four-byte character
				$ascii1 = ord($utf8[$i+1]);
				$ascii2 = ord($utf8[$i+2]);
				$ascii3 = ord($utf8[$i+3]);
				$unicode = (15 & $ascii) * 262144 +
						   (63 & $ascii1) * 4096 +
						   (63 & $ascii2) * 64 +
						   (63 & $ascii3);
				$result .= "&#$unicode;";
				$i += 3;
			}
		}
		return $result;
	}

	function buildThread($id, $return=false, $mod=false) {
		global $board, $config;
		$id = round($id);
		
		$query = prepare(sprintf("SELECT `id`,`thread`,`subject`,`name`,`email`,`trip`,`body`,`time`,`thumb`,`thumbwidth`,`thumbheight`,`file`,`filewidth`,`fileheight`,`filesize`,`filename`,`ip`,`sticky`,`locked` FROM `posts_%s` WHERE (`thread` IS NULL AND `id` = :id) OR `thread` = :id ORDER BY `thread`,`time`", $board['uri']));
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		$query->execute() or error(db_error($query));
		
		while($post = $query->fetch()) {
			if(!isset($thread)) {
				$thread = new Thread($post['id'], $post['subject'], $post['email'], $post['name'], $post['trip'], $post['body'], $post['time'], $post['thumb'], $post['thumbwidth'], $post['thumbheight'], $post['file'], $post['filewidth'], $post['fileheight'], $post['filesize'], $post['filename'], $post['ip'], $post['sticky'], $post['locked'], $mod ? '?/' : $config['root'], $mod);
			} else {
				$thread->add(new Post($post['id'], $thread->id, $post['subject'], $post['email'], $post['name'], $post['trip'], $post['body'], $post['time'], $post['thumb'], $post['thumbwidth'], $post['thumbheight'], $post['file'], $post['filewidth'], $post['fileheight'], $post['filesize'], $post['filename'], $post['ip'], $mod ? '?/' : $config['root'], $mod));
			}
		}
		
		// Check if any posts were found
		if(!isset($thread)) error($config['error']['nonexistant']);
		
		$body = Element('thread.html', Array(
			'board'=>$board, 
			'body'=>$thread->build(),
			'config' => $config,
			'id' => $id,
			'mod' => $mod,
			'boardlist' => createBoardlist($mod),
			'hidden_inputs' => $content['hidden_inputs'] = createHiddenInputs(),
			'return' => ($mod ? '?' . $board['url'] . $config['file_index'] : $config['root'] . $board['uri'] . '/' . $config['file_index'])
		));
			
		if($return)
			return $body;
		else
			@file_put_contents($board['dir'] . $config['dir']['res'] . sprintf($config['file_page'], $id), $body) or error("Couldn't write to file.");
	}
	
	 function rrmdir($dir) {
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (filetype($dir."/".$object) == "dir")
						rrmdir($dir."/".$object);
					else
						unlink($dir."/".$object);
				}
			}
			reset($objects);
			rmdir($dir);
		}
	} 
 
	function generate_tripcode ( $name, $length = 10 ) {
		global $config;
		$name = stripslashes ( $name );
		$t = explode('#', $name);
		$nameo = $t[0];
		if ( isset ( $t[1] ) || isset ( $t[2] ) ) {
			$trip = ( ( strlen ( $t[1] ) > 0 ) ? $t[1] : $t[2] );
			if ( ( function_exists ( 'mb_convert_encoding' ) ) ) {
				# mb_substitute_character('none');
				$recoded_cap = mb_convert_encoding ( $trip, 'Shift_JIS', 'UTF-8' );
			}
			$trip = ( ( ! empty ( $recoded_cap ) ) ? $recoded_cap : $trip );
			$salt = substr ( $trip.'H.', 1, 2 );
			$salt = preg_replace ( '/[^\.-z]/', '.', $salt );
			$salt = strtr ( $salt, ':;<=>?@[\]^_`', 'ABCDEFGabcdef' );
			if ( isset ( $t[2] ) ) {
				// secure
				$trip = '!!' . substr ( crypt ( $trip, $config['secure_trip_salt'] ), ( -1 * $length ) );
			} else {
				// insecure
				$trip = '!' . substr ( crypt ( $trip, $salt ), ( -1 * $length ) );
			}
		}
		if ( isset ( $trip ) ) {
			return array ( $nameo, $trip );
		} else {
			return array ( $nameo );
		}
	}

	// Highest common factor
	function hcf($a, $b){
		$gcd = 1;
		if ($a>$b) {
			$a = $a+$b;
			$b = $a-$b;
			$a = $a-$b;
		}
		if ($b==(round($b/$a))*$a) 
			$gcd=$a;
		else {
			for($i=round($a/2);$i;$i--) {
				if ($a == round($a/$i)*$i && $b == round($b/$i)*$i) {
					$gcd = $i;
					$i = false;
				}
			}
		}
		return $gcd;
	}

	function fraction($numerator, $denominator, $sep) {
		$gcf = hcf($numerator, $denominator);
		$numerator = $numerator / $gcf;
		$denominator = $denominator / $gcf;

		return "{$numerator}{$sep}{$denominator}";
	}

	/*********************************************/
	/* Fonction: imagecreatefrombmp              */
	/* Author:   DHKold                          */
	/* Contact:  admin@dhkold.com                */
	/* Date:     The 15th of June 2005           */
	/* Version:  2.0B                            */
	/*********************************************/

	function imagecreatefrombmp($filename) {
	   if (! $f1 = fopen($filename,"rb")) return FALSE;
	   $FILE = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1,14));
	   if ($FILE['file_type'] != 19778) return FALSE;
	   $BMP = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel'.
					 '/Vcompression/Vsize_bitmap/Vhoriz_resolution'.
					 '/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1,40));
	   $BMP['colors'] = pow(2,$BMP['bits_per_pixel']);
	   if ($BMP['size_bitmap'] == 0) $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
	   $BMP['bytes_per_pixel'] = $BMP['bits_per_pixel']/8;
	   $BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']);
	   $BMP['decal'] = ($BMP['width']*$BMP['bytes_per_pixel']/4);
	   $BMP['decal'] -= floor($BMP['width']*$BMP['bytes_per_pixel']/4);
	   $BMP['decal'] = 4-(4*$BMP['decal']);
	   if ($BMP['decal'] == 4) $BMP['decal'] = 0;

	   $PALETTE = array();
	   if ($BMP['colors'] < 16777216)
	   {
		$PALETTE = unpack('V'.$BMP['colors'], fread($f1,$BMP['colors']*4));
	   }

	   $IMG = fread($f1,$BMP['size_bitmap']);
	   $VIDE = chr(0);

	   $res = imagecreatetruecolor($BMP['width'],$BMP['height']);
	   $P = 0;
	   $Y = $BMP['height']-1;
	   while ($Y >= 0)
	   {
		$X=0;
		while ($X < $BMP['width'])
		{
		 if ($BMP['bits_per_pixel'] == 24)
			$COLOR = unpack("V",substr($IMG,$P,3).$VIDE);
		 elseif ($BMP['bits_per_pixel'] == 16)
		 {  
			$COLOR = unpack("n",substr($IMG,$P,2));
			$COLOR[1] = $PALETTE[$COLOR[1]+1];
		 }
		 elseif ($BMP['bits_per_pixel'] == 8)
		 {  
			$COLOR = unpack("n",$VIDE.substr($IMG,$P,1));
			$COLOR[1] = $PALETTE[$COLOR[1]+1];
		 }
		 elseif ($BMP['bits_per_pixel'] == 4)
		 {
			$COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
			if (($P*2)%2 == 0) $COLOR[1] = ($COLOR[1] >> 4) ; else $COLOR[1] = ($COLOR[1] & 0x0F);
			$COLOR[1] = $PALETTE[$COLOR[1]+1];
		 }
		 elseif ($BMP['bits_per_pixel'] == 1)
		 {
			$COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
			if     (($P*8)%8 == 0) $COLOR[1] =  $COLOR[1]        >>7;
			elseif (($P*8)%8 == 1) $COLOR[1] = ($COLOR[1] & 0x40)>>6;
			elseif (($P*8)%8 == 2) $COLOR[1] = ($COLOR[1] & 0x20)>>5;
			elseif (($P*8)%8 == 3) $COLOR[1] = ($COLOR[1] & 0x10)>>4;
			elseif (($P*8)%8 == 4) $COLOR[1] = ($COLOR[1] & 0x8)>>3;
			elseif (($P*8)%8 == 5) $COLOR[1] = ($COLOR[1] & 0x4)>>2;
			elseif (($P*8)%8 == 6) $COLOR[1] = ($COLOR[1] & 0x2)>>1;
			elseif (($P*8)%8 == 7) $COLOR[1] = ($COLOR[1] & 0x1);
			$COLOR[1] = $PALETTE[$COLOR[1]+1];
		 }
		 else
			return FALSE;
		 imagesetpixel($res,$X,$Y,$COLOR[1]);
		 $X++;
		 $P += $BMP['bytes_per_pixel'];
		}
		$Y--;
		$P+=$BMP['decal'];
	   }
	   fclose($f1);

	 return $res;
	}
	
	function getPostByHash($hash) {
		global $board;
		$query = prepare(sprintf("SELECT `id`,`thread` FROM `posts_%s` WHERE `filehash` = :hash", $board['uri']));
		$query->bindValue(':hash', $hash, PDO::PARAM_STR);
		$query->execute() or error(db_error($query));
		
		if($post = $query->fetch()) {
			return $post;
		}
		
		return false;
	}
	
	function undoImage($post) {
		if($post['has_file'])
			@unlink($post['file']);
			@unlink($post['thumb']);
	}
	
	function createimage($type, $source_pic) {
		global $config;
		
		$image = false;
		switch($type) {
			case 'jpg':
			case 'jpeg':
				if(!$image = @imagecreatefromjpeg($source_pic)) {
					unlink($source_pic);
					error($config['error']['invalidimg']);
				}
				break;
			case 'png':
				if(!$image = @imagecreatefrompng($source_pic)) {
					unlink($source_pic);
					error($config['error']['invalidimg']);
				}
				break;
			case 'gif':
				if(!$image = @imagecreatefromgif($source_pic)) {
					unlink($source_pic);
					error($config['error']['invalidimg']);
				}
				break;
			case 'bmp':
				if(!$image = @imagecreatefrombmp($source_pic)) {
					unlink($source_pic);
					error($config['error']['invalidimg']);
				}
				break;
			default:
				error('Unknwon file extension.');
		}
		return $image;
	}

	function resize($src, $width, $height, $destination_pic, $max_width, $max_height) {
		$return = Array();

		$x_ratio = $max_width / $width;
		$y_ratio = $max_height / $height;

		if(($width <= $max_width) && ($height <= $max_height)) {
			$tn_width = $width;
			$tn_height = $height;
			} elseif (($x_ratio * $height) < $max_height) {
				$tn_height = ceil($x_ratio * $height);
				$tn_width = $max_width;
			} else {
				$tn_width = ceil($y_ratio * $width);
				$tn_height = $max_height;
		}

		$return['width'] = $tn_width;
		$return['height'] = $tn_height;

		$tmp = imagecreatetruecolor($tn_width, $tn_height);
		imagecolortransparent($tmp, imagecolorallocatealpha($tmp, 0, 0, 0, 0));
		imagealphablending($tmp, false);
		imagesavealpha($tmp, true);

		imagecopyresampled($tmp, $src, 0, 0, 0, 0, $tn_width, $tn_height, $width, $height);

		imagepng($tmp, $destination_pic, 4);
		imagedestroy($src);
		imagedestroy($tmp);

		return $return;
	}

	function imagebmp(&$img, $filename='') {
		$widthOrig = imagesx($img);
		$widthFloor = ((floor($widthOrig/16))*16);
		$widthCeil = ((ceil($widthOrig/16))*16);
		$height = imagesy($img);

		$size = ($widthCeil*$height*3)+54;

		// Bitmap File Header
		$result = 'BM';	 // header (2b)
		$result .= int_to_dword($size); // size of file (4b)
		$result .= int_to_dword(0); // reserved (4b)
		$result .= int_to_dword(54); // byte location in the file which is first byte of IMAGE (4b)
		// Bitmap Info Header
		$result .= int_to_dword(40); // Size of BITMAPINFOHEADER (4b)
		$result .= int_to_dword($widthCeil); // width of bitmap (4b)
		$result .= int_to_dword($height); // height of bitmap (4b)
		$result .= int_to_word(1);	// biPlanes = 1 (2b)
		$result .= int_to_word(24); // biBitCount = {1 (mono) or 4 (16 clr ) or 8 (256 clr) or 24 (16 Mil)} (2b
		$result .= int_to_dword(0); // RLE COMPRESSION (4b)
		$result .= int_to_dword(0); // width x height (4b)
		$result .= int_to_dword(0); // biXPelsPerMeter (4b)
		$result .= int_to_dword(0); // biYPelsPerMeter (4b)
		$result .= int_to_dword(0); // Number of palettes used (4b)
		$result .= int_to_dword(0); // Number of important colour (4b)

		// is faster than chr()
		$arrChr = array();
		for($i=0; $i<256; $i++){
		$arrChr[$i] = chr($i);
		}

		// creates image data
		$bgfillcolor = array('red'=>0, 'green'=>0, 'blue'=>0);

		// bottom to top - left to right - attention blue green red !!!
		$y=$height-1;
		for ($y2=0; $y2<$height; $y2++) {
			for ($x=0; $x<$widthFloor;	) {
				$rgb = imagecolorsforindex($img, imagecolorat($img, $x++, $y));
				$result .= $arrChr[$rgb['blue']].$arrChr[$rgb['green']].$arrChr[$rgb['red']];
				$rgb = imagecolorsforindex($img, imagecolorat($img, $x++, $y));
				$result .= $arrChr[$rgb['blue']].$arrChr[$rgb['green']].$arrChr[$rgb['red']];
				$rgb = imagecolorsforindex($img, imagecolorat($img, $x++, $y));
				$result .= $arrChr[$rgb['blue']].$arrChr[$rgb['green']].$arrChr[$rgb['red']];
				$rgb = imagecolorsforindex($img, imagecolorat($img, $x++, $y));
				$result .= $arrChr[$rgb['blue']].$arrChr[$rgb['green']].$arrChr[$rgb['red']];
				$rgb = imagecolorsforindex($img, imagecolorat($img, $x++, $y));
				$result .= $arrChr[$rgb['blue']].$arrChr[$rgb['green']].$arrChr[$rgb['red']];
				$rgb = imagecolorsforindex($img, imagecolorat($img, $x++, $y));
				$result .= $arrChr[$rgb['blue']].$arrChr[$rgb['green']].$arrChr[$rgb['red']];
				$rgb = imagecolorsforindex($img, imagecolorat($img, $x++, $y));
				$result .= $arrChr[$rgb['blue']].$arrChr[$rgb['green']].$arrChr[$rgb['red']];
				$rgb = imagecolorsforindex($img, imagecolorat($img, $x++, $y));
				$result .= $arrChr[$rgb['blue']].$arrChr[$rgb['green']].$arrChr[$rgb['red']];
			}
			for ($x=$widthFloor; $x<$widthCeil; $x++) {
				$rgb = ($x<$widthOrig) ? imagecolorsforindex($img, imagecolorat($img, $x, $y)) : $bgfillcolor;
				$result .= $arrChr[$rgb['blue']].$arrChr[$rgb['green']].$arrChr[$rgb['red']];
			}
			$y--;
		}

		// see imagegif
		if($filename == '') {
			echo $result;
		} else {
			$file = fopen($filename, 'wb');
			fwrite($file, $result);
			fclose($file);
		}
	}
	// imagebmp helpers
	function int_to_dword($n) {
		return chr($n & 255).chr(($n >> 8) & 255).chr(($n >> 16) & 255).chr(($n >> 24) & 255);
	}
	function int_to_word($n) {
		return chr($n & 255).chr(($n >> 8) & 255);
	}
?>
