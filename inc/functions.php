<?php
	function sprintf3($str, $vars, $delim = '%') {
		$replaces = array();
		foreach($vars as $k => $v) {
			$replaces[$delim . $k . $delim] = $v;
		}
		return str_replace(array_keys($replaces),
		                   array_values($replaces), $str);
	}
	
	function setupBoard($array) {
		global $board;
		
		$board = Array(
		'id' => $array['id'],
		'uri' => $array['uri'],
		'name' => $array['title'],
		'title' => $array['subtitle']);
		
		$board['dir'] = sprintf(BOARD_PATH, $board['uri']);
		$board['url'] = sprintf(BOARD_ABBREVIATION, $board['uri']);
		
		if(!file_exists($board['dir'])) mkdir($board['dir'], 0777);
		if(!file_exists($board['dir'] . DIR_IMG)) @mkdir($board['dir'] . DIR_IMG, 0777) or error("Couldn't create " . DIR_IMG . ". Check permissions.", true);
		if(!file_exists($board['dir'] . DIR_THUMB)) @mkdir($board['dir'] . DIR_THUMB, 0777) or error("Couldn't create " . DIR_THUMB . ". Check permissions.", true);
		if(!file_exists($board['dir'] . DIR_RES)) @mkdir($board['dir'] . DIR_RES, 0777) or error("Couldn't create " . DIR_RES . ". Check permissions.", true);
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
		$query = query("SELECT * FROM `boards`") or error(db_error());
		$boards = $query->fetchAll();
		return $boards;
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
		} elseif($difference < 60*60*24*7*52) {
			return ($num = round($difference/(60*60*24*7))) . ' week' . ($num != 1 ? 's' : '');
		} else {
			return ($num = round($difference/(60*60*24*7*52))) . ' year' . ($num != 1 ? 's' : '');
		}
	}
	
	function formatDate($timestamp) {
		return date('jS F, Y', $timestamp);
	}
	
	function checkBan() {
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
		'</strong>, and ' . 
			($ban['expires'] ?
				'expires on <strong>' .
					formatDate($ban['expires']) .
				'</strong>, which is ' . until($ban['expires']) . ' from now'
			: '<em>will not expire</em>' ) .
		'.</p>
		<p>Your IP address is <strong>' . $_SERVER['REMOTE_ADDR'] . '</strong>.</p>
	</div>';
			
			// Show banned page and exit
			die(Element('page.html', Array(
					'index' => ROOT,
					'title' => 'Banned',
					'subtitle' => 'You are banned!',
					'body' => $body
				)
			));
		}
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
		
		$query = prepare(sprintf("INSERT INTO `posts_%s` VALUES ( NULL, :thread, :subject, :email, :name, :trip, :body, :time, :time, :thumb, :thumbwidth, :thumbheight, :file, :width, :height, :filesize, :filename, :filehash, :password, :ip, :sticky)", $board['uri']));
		
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

	function index($page, $mod=false) {
		global $board;

		$body = '';
		$offset = round($page*THREADS_PER_PAGE-THREADS_PER_PAGE);

		sql_open();
		
		$query = prepare(sprintf("SELECT * FROM `posts_%s` WHERE `thread` IS NULL ORDER BY `sticky` DESC, `bump` DESC LIMIT ?,?", $board['uri']));
		$query->bindValue(1, $offset, PDO::PARAM_INT);
		$query->bindValue(2, THREADS_PER_PAGE, PDO::PARAM_INT);
		$query->execute() or error(db_error($query));
		
		if($query->rowcount() < 1 && $page > 1) return false;
		while($th = $query->fetch()) {
			$thread = new Thread($th['id'], $th['subject'], $th['email'], $th['name'], $th['trip'], $th['body'], $th['time'], $th['thumb'], $th['thumbwidth'], $th['thumbheight'], $th['file'], $th['filewidth'], $th['fileheight'], $th['filesize'], $th['filename'], $th['ip'], $th['sticky'], $mod ? '?/' : ROOT, $mod);

			$posts = prepare(sprintf("SELECT `id`, `subject`, `email`, `name`, `trip`, `body`, `time`, `thumb`, `thumbwidth`, `thumbheight`, `file`, `filewidth`, `fileheight`, `filesize`, `filename`,`ip` FROM `posts_%s` WHERE `thread` = ? ORDER BY `time` DESC LIMIT ?", $board['uri']));
			$posts->bindValue(1, $th['id']);
			$posts->bindValue(2, THREADS_PREVIEW, PDO::PARAM_INT);
			$posts->execute() or error(db_error($posts));
			
			if($posts->rowCount() == THREADS_PREVIEW) {
				$count = prepare(sprintf("SELECT COUNT(`id`) as `num` FROM `posts_%s` WHERE `thread` = ?", $board['uri']));
				$count->bindValue(1, $th['id']);
				$count->execute() or error(db_error($count));
				
				$count = $count->fetch();
				$omitted = $count['num'] - THREADS_PREVIEW;
				$thread->omitted = $omitted;
				unset($count);
				unset($omitted);
			}
			
			while($po = $posts->fetch()) {
				$thread->add(new Post($po['id'], $th['id'], $po['subject'], $po['email'], $po['name'], $po['trip'], $po['body'], $po['time'], $po['thumb'], $po['thumbwidth'], $po['thumbheight'], $po['file'], $po['filewidth'], $po['fileheight'], $po['filesize'], $po['filename'], $po['ip'], $mod ? '?/' : ROOT, $mod));
			}

			$thread->posts = array_reverse($thread->posts);
			$body .= $thread->build(true);
		}
		
		return Array('button'=>BUTTON_NEWTOPIC, 'board'=>$board, 'body'=>$body, 'post_url' => POST_URL, 'index' => ROOT);
	}
	
	function getPages($mod=false) {
		global $board;
		
		// Count threads
		$query = query(sprintf("SELECT COUNT(`id`) as `num` FROM `posts_%s` WHERE `thread` IS NULL", $board['uri'])) or error(db_error());
		
		$count = current($query->fetch());
		$count = floor((THREADS_PER_PAGE + $count - 1) / THREADS_PER_PAGE);

		$pages = Array();
		for($x=0;$x<$count && $x<MAX_PAGES;$x++) {
			$pages[] = Array('num' => $x+1, 'link' => $x==0 ? ($mod ? '?/' : ROOT) . $board['dir'] . FILE_INDEX : ($mod ? '?/' : ROOT) . $board['dir'] . sprintf(FILE_PAGE, $x+1));
		}
		
		return $pages;
	}

	function buildIndex() {
		global $board;
		sql_open();
		
		$pages = getPages();

		$page = 1;
		while($page <= MAX_PAGES && $content = index($page)) {
			$filename = $board['dir'] . ($page==1 ? FILE_INDEX : sprintf(FILE_PAGE, $page));
			if(file_exists($filename)) $md5 = md5_file($filename);

			$content['pages'] = $pages;
			@file_put_contents($filename, Element('index.html', $content)) or error("Couldn't write to file.");
			
			if(isset($md5) && $md5 == md5_file($filename)) {
				break;
			}
			$page++;
		}
		if($page < MAX_PAGES) {
			for(;$page<=MAX_PAGES;$page++) {
				$filename = $page==1 ? FILE_INDEX : sprintf(FILE_PAGE, $page);
				@unlink($filename);
			}
		}
	}

	function markup(&$body) {
		global $board;
		
		$body = utf8tohtml($body, true);
		
		if(MARKUP_URLS)
			$body = preg_replace(URL_REGEX, "<a href=\"$0\">$0</a>", $body);
			
		if(AUTO_UNICODE) {
			$body = str_replace('...', '…', $body);
			$body = str_replace('<--', '←', $body);

			// En and em- dashes are rendered exactly the same in
			// most monospace fonts (they look the same in code
			// editors).
			$body = str_replace('---', '—', $body); // em dash
			$body = str_replace('--', '–', $body); // en dash
		}

		// Cites
		if(preg_match_all('/(^|\s)&gt;&gt;([0-9]+?)(\s|$)/', $body, $cites)) {
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
					$replacement = '<a onclick="highlightReply(\''.$cite.'\');" href="' . ROOT . $board['dir'] . DIR_RES . ($post['thread']?$post['thread']:$post['id']) . '.html#' . $cite . '">&gt;&gt;' . $cite . '</a>';
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
		
		if(WIKI_MARKUP) {
			$body = preg_replace("/(^|\n)==(.+?)==\n?/m", "<h2>$2</h2>", $body);
			$body = preg_replace("/'''(.+?)'''/m", "<strong>$1</strong>", $body);
			$body = preg_replace("/''(.+?)''/m", "<em>$1</em>", $body);
		}
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
		global $board;
		$id = round($id);
		
		$query = prepare(sprintf("SELECT `id`,`thread`,`subject`,`name`,`email`,`trip`,`body`,`time`,`thumb`,`thumbwidth`,`thumbheight`,`file`,`filewidth`,`fileheight`,`filesize`,`filename`,`ip`,`sticky` FROM `posts_%s` WHERE (`thread` IS NULL AND `id` = :id) OR `thread` = :id ORDER BY `thread`,`time`", $board['uri']));
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		$query->execute() or error(db_error($query));
		
		while($post = $query->fetch()) {
			if(!isset($thread)) {
				$thread = new Thread($post['id'], $post['subject'], $post['email'], $post['name'], $post['trip'], $post['body'], $post['time'], $post['thumb'], $post['thumbwidth'], $post['thumbheight'], $post['file'], $post['filewidth'], $post['fileheight'], $post['filesize'], $post['filename'], $post['ip'], $post['sticky'], $mod ? '?/' : ROOT, $mod);
			} else {
				$thread->add(new Post($post['id'], $thread->id, $post['subject'], $post['email'], $post['name'], $post['trip'], $post['body'], $post['time'], $post['thumb'], $post['thumbwidth'], $post['thumbheight'], $post['file'], $post['filewidth'], $post['fileheight'], $post['filesize'], $post['filename'], $post['ip'], $mod ? '?/' : ROOT, $mod));
			}
		}
		
		// Check if any posts were found
		if(!isset($thread)) error(ERROR_NONEXISTANT);
		
		$body = Element('thread.html', Array(
			'button'=>BUTTON_REPLY,
			'board'=>$board, 
			'body'=>$thread->build(),
			'post_url' => POST_URL,
			'index' => ROOT,
			'id' => $id,
			'mod' => $mod,
			'return' => ($mod ? '?' . $board['url'] . FILE_INDEX : ROOT . $board['uri'] . '/' . FILE_INDEX)
		));
			
		if($return)
			return $body;
		else
			@file_put_contents($board['dir'] . DIR_RES . sprintf(FILE_PAGE, $id), $body) or error("Couldn't write to file.");
	}
	
	function generate_tripcode ( $name, $length = 10 ) {
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
				$trip = '!!' . substr ( crypt ( $trip, '@#$%^&*()' ), ( -1 * $length ) );
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

	function createimage($type, $source_pic) {
		$image = false;
		switch($type) {
			case 'jpg':
			case 'jpeg':
				if(!$image = @imagecreatefromjpeg($source_pic)) {
					unlink($source_pic);
					error(ERR_INVALIDIMG);
				}
				break;
			case 'png':
				if(!$image = @imagecreatefrompng($source_pic)) {
					unlink($source_pic);
					error(ERR_INVALIDIMG);
				}
				break;
			case 'gif':
				if(!$image = @imagecreatefromgif($source_pic)) {
					unlink($source_pic);
					error(ERR_INVALIDIMG);
				}
				break;
			case 'bmp':
				if(!$image = @imagecreatefrombmp($source_pic)) {
					unlink($source_pic);
					error(ERR_INVALIDIMG);
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