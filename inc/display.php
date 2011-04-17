<?php
	if($_SERVER['SCRIPT_FILENAME'] == str_replace('\\', '/', __FILE__)) {
		// You cannot request this file directly.
		header('Location: ../', true, 302);
		exit;
	}
	
	/*
		Stuff to help with the display.
	*/
	
	
	/* 
		joaoptm78@gmail.com
		http://www.php.net/manual/en/function.filesize.php#100097
	*/
	function format_bytes($size) {
		$units = array(' B', ' KB', ' MB', ' GB', ' TB');
		for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
		return round($size, 2).$units[$i];
	}
	
	function commaize($n) {
		$n = strval($n);
		return (intval($n) < 1000) ? $n : commaize(substr($n, 0, -3)) . ',' . substr($n, -3);
	}
	
	function doBoardListPart($list, $root) {
		global $config;
		
		$body = '';
		foreach($list as $board) {
			if(is_array($board))
				$body .= ' [' . doBoardListPart($board, $root) . '] ';
			else {
				if(($key = array_search($board, $list)) && gettype($key) == 'string') {
					$body .= ' <a href="' . $board . '">' . $key . '</a> /';
				} else {			
					$body .= ' <a href="' . $root . $board . '/' . $config['file_index'] . '">' . $board . '</a> /';
				}
			}
		}
		$body = preg_replace('/\/$/', '', $body);
		
		return $body;
	}
	
	function createBoardlist($mod=false) {
		global $config;
		
		if(!isset($config['boards'])) return Array('top'=>'','bottom'=>'');
		
		$body = doBoardListPart($config['boards'], $mod?'?/':$config['root']);
		if(!preg_match('/\] $/', $body))
			$body = '[' . $body . ']';
		
		$body = trim($body);
		
		return Array(
			'top' => '<div class="boardlist">' . $body . '</div>',
			'bottom' => '<div class="boardlist bottom">' . $body . '</div>'
		);
	}

	function error($message) {
		global $board, $mod, $config;
		
		if(function_exists('sql_close')) sql_close();
		die(Element('page.html', Array(
			'config'=>$config,
			'title'=>'Error',
			'subtitle'=>'An error has occured.',
			'body'=>"<center>" .
			        "<h2>$message</h2>" .
				(isset($board) ? 
					"<p><a href=\"" . $config['root'] .
						($mod ? $config['file_mod'] . '?/' : '') .
						$board['dir'] . $config['file_index'] . "\">Go back</a>.</p>" : '').
			        "</center>"
		)));
	}
	
	function loginForm($error=false, $username=false, $redirect=false) {
		global $config;
		
		if(function_exists('sql_close')) sql_close();
		die(Element('page.html', Array(
			'index'=>$config['root'],
			'title'=>'Login',
			'config'=>$config,
			'body'=>Element('login.html', Array(
				'config'=>$config,
				'error'=>$error,
				'username'=>$username,
				'redirect'=>$redirect
				)
			)
		)));
	}
	
	function pm_snippet($body, $len=null) {
		global $config;
		
		if(!isset($len))
			$len = $config['mod']['snippet_length'];
		
		// Replace line breaks with some whitespace
		$body = str_replace('<br/>', '  ', $body);
		
		// Strip tags
		$body = strip_tags($body);
		
		// Unescape HTML characters, to avoid splitting them in half
		$body = html_entity_decode_utf8($body);
		
		$body = substr($body, 0, $len) . (strlen($body) > $len ? '…' : '');
		
		// Re-escape the characters.
		return '<em>' . utf8tohtml($body) . '</em>';
	}
	
	function capcode($cap) {
		global $config;
		
		if(isset($config['custom_capcode'][$cap])) {
			if(is_array($config['custom_capcode'][$cap]))
				return sprintf($config['custom_capcode'][$cap][0], $cap);
			return sprintf($config['custom_capcode'][$cap], $cap);
		}
		
		return sprintf($config['capcode'], $cap);
	}
	
	function truncate($body, $url) {
		global $config;
		
		$original_body = $body;
		
		$lines = substr_count($body, '<br/>');
		
		// Limit line count
		if($lines > $config['body_truncate']) {
			if(preg_match('/(((.*?)<br\/>){' . $config['body_truncate'] . '})/', $body, $m))
				$body = $m[0];
		}
		
		$body = substr($body, 0, $config['body_truncate_char']);
		
		if($body != $original_body) {
			// Remove any corrupt tags at the end
			$body = preg_replace('/<([\w]+)?([^>]*)?$/', '', $body);
			
			// Open tags
			if(preg_match_all('/<([\w]+)[^>]*>/', $body, $open_tags)) {
				
				$tags = Array();
				for($x=0;$x<count($open_tags[0]);$x++) {
					if(!preg_match('/\/(\s+)?>$/', $open_tags[0][$x]))
						$tags[] = $open_tags[1][$x];
				}
				
				// List successfully closed tags
				if(preg_match_all('/(<\/([\w]+))>/', $body, $closed_tags)) {
					for($x=0;$x<count($closed_tags[0]);$x++) {
						unset($tags[array_search($closed_tags[1][$x], $tags)]);
					}
				}
				
				// Close any open tags
				foreach($tags as &$tag) {
					$body .= "</{$tag}>";
				}
			}
			$body .= '<span class="toolong">Post too long. Click <a href="' . $url . '">here</a> to view the full text.</span>';
		}
		
		return $body;
	}
	
	function confirmLink($text, $title, $confirm, $href) {
		global $config, $mod;
		if($config['mod']['server-side_confirm'])
			return '<a onclick="if(confirm(\'' . htmlentities(addslashes($confirm)) . '\')) document.location=\'?/' . htmlentities(addslashes($href)) . '\';return false;" title="' . htmlentities($title) . '" href="?/confirm/' . $href . '">' . $text . '</a>';
		else
			return '<a onclick="return confirm(\'' . htmlentities(addslashes($confirm)) . '\')" title="' . htmlentities($title) . '" href="?/' . $href . '">' . $text . '</a>';
	}
	
	class Post {
		public function __construct($id, $thread, $subject, $email, $name, $trip, $capcode, $body, $time, $thumb, $thumbx, $thumby, $file, $filex, $filey, $filesize, $filename, $ip, $root=null, $mod=false) {
			global $config;
			if(!isset($root)) $root = $config['root'];
			
			$this->id = $id;
			$this->thread = $thread;
			$this->subject = utf8tohtml($subject);
			$this->email = $email;
			$this->name = utf8tohtml($name);
			$this->trip = $trip;
			$this->capcode = $capcode;
			$this->body = $body;
			$this->time = $time;
			$this->thumb = $thumb;
			$this->thumbx = $thumbx;
			$this->thumby = $thumby;
			$this->file = $file;
			$this->filex = $filex;
			$this->filey = $filey;
			$this->filesize = $filesize;
			$this->filename = $filename;
			$this->ip = $ip;
			$this->root = $root;
			$this->mod = $mod;
			
			if($this->mod)
				// Fix internal links
				// Very complicated regex
				$this->body = preg_replace(
					'/<a((([a-zA-Z]+="[^"]+")|[a-zA-Z]+=[a-zA-Z]+|\s)*)href="' . preg_quote($config['root'], '/') . '(' . sprintf(preg_quote($config['board_path'], '/'), '\w+') . ')/',
					'<a $1href="?/$4',
					$this->body
				);
		}
		public function link($pre = '') {
			global $config, $board;
			
			return $this->root . $board['dir'] . $config['dir']['res'] . $this->thread . '.html' . '#' . $pre . $this->id;
		}
		public function postControls() {
			global $board, $config;
			
			$built = '';
			if($this->mod) {
				// Mod controls (on posts)
				$built .= '<span class="controls">';
				
				// Delete
				if($this->mod['type'] >= $config['mod']['delete'])
					$built .= ' ' . confirmLink($config['mod']['link_delete'], 'Delete', 'Are you sure you want to delete this?', $board['uri'] . '/delete/' . $this->id);
				
				// Delete all posts by IP
				if($this->mod['type'] >= $config['mod']['deletebyip'])
					$built .= ' ' . confirmLink($config['mod']['link_deletebyip'], 'Delete all posts by IP', 'Are you sure you want to delete all posts by IP?', $board['uri'] . '/deletebyip/' . $this->id);
				
				// Ban
				if($this->mod['type'] >= $config['mod']['ban'])
					$built .= ' <a title="Ban" href="?/' . $board['uri'] . '/ban/' . $this->id . '">' . $config['mod']['link_ban'] . '</a>';
				
				// Ban & Delete
				if($this->mod['type'] >= $config['mod']['bandelete'])
					$built .= ' <a title="Ban & Delete" href="?/' . $board['uri'] . '/ban&amp;delete/' . $this->id . '">' . $config['mod']['link_bandelete'] . '</a>';
				
				// Delete file (keep post)
				if(!empty($this->file) && $this->mod['type'] >= $config['mod']['deletefile'])
					$built .= ' <a title="Remove file" href="?/' . $board['uri'] . '/deletefile/' . $this->id . '">' . $config['mod']['link_deletefile'] . '</a>';
				
				$built .= '</span>';
			}
			return $built;
		}
		
		public function build($index=false) {
			global $board, $config;
			
			$built =	'<div class="post reply" id="reply_' . $this->id . '">' . 
						'<p class="intro"' . (!$index?' id="' . $this->id . '"':'') . '>' . 
			// Delete
				'<input type="checkbox" class="delete" name="delete_' . $this->id . '" id="delete_' . $this->id . '" /><label for="delete_' . $this->id . '">';
			
			// Subject
			if(!empty($this->subject))
				$built .= '<span class="subject">' . $this->subject . '</span> ';
			// Email
			if(!empty($this->email))
				$built .=  '<a class="email" href="mailto:' . $this->email . '">';
			// Name
			$built .= '<span class="name"' .
				(!empty($this->capcode) && isset($config['custom_capcode'][$this->capcode][1]) ?
					' style="' . $config['custom_capcode'][$this->capcode][1] . '"'
				: '')
			. '>' . $this->name . '</span>'
			// Trip
			. (!empty($this->trip) ? ' <span class="trip"' . 
				(!empty($this->capcode) && isset($config['custom_capcode'][$this->capcode][2]) ?
					' style="' . $config['custom_capcode'][$this->capcode][2] . '"'
				: '')
			. '>'.$this->trip.'</span>':'')
			// End email
			. (!empty($this->email)? '</a>' : '')
			// Capcode
			. (!empty($this->capcode) ? capcode($this->capcode) : '');
			
			// IP Address
			if($this->mod && $this->mod['type'] >= $config['mod']['show_ip']) {
				$built .= ' [<a style="margin:0;" href="?/IP/' . $this->ip . '">' . $this->ip . '</a>]';
			}
			
			// Date/time
			$built .= ' ' . date($config['post_date'], $this->time);
			
			// End delete
			$built .= '</label>'
			
			// Poster ID
			. ($config['poster_ids'] ?
				' ID: ' . poster_id($this->ip, $this->thread)
			: '')
			
			. ' <a class="post_no"' . 
			// JavaScript highlight
				($index?'':' onclick="highlightReply(' . $this->id . ');"') .
				' href="' . $this->link() . '">No.</a>' . 
			// JavaScript cite
				'<a class="post_no"' . ($index?'':' onclick="citeReply(' . $this->id . ');"') . ' href="' . ($index ? $this->link('q') : 'javascript:void(0);') . '">'.$this->id.'</a>' . 
			'</p>';
		
			// File info
			if(!empty($this->file) && $this->file != 'deleted') {
				$built .= '<p class="fileinfo">File: <a href="'	. $config['uri_img'] . $this->file .'">' . $this->file . '</a> <span class="unimportant">(' . 
			// Filesize
				format_bytes($this->filesize) .
			// File dimensions
			($this->filex && $this->filey ?
				', ' . $this->filex . 'x' . $this->filey
			: '' );
			// Aspect Ratio
			if($config['show_ratio'] && $this->filex && $this->filey) {
				$fraction = fraction($this->filex, $this->filey, ':');
				$built .= ', ' . $fraction;
			}
			// Filename
				$built .= ', ' . $this->filename . ')</span></p>' .
				
			// Thumbnail
				'<a href="' . $config['uri_img'] . $this->file.'"><img src="' . $config['uri_thumb'] . $this->thumb.'" style="width:'.$this->thumbx.'px;height:'.$this->thumby.'px;" /></a>';
			} elseif($this->file == 'deleted') {
				$built .= '<img src="' . $config['image_deleted'] . '" />';
			}
			
			$built .= $this->postControls();
			
			// Body
			$built .= '<p class="body">' . ($index ? truncate($this->body, $this->link()) : $this->body) . '</p></div><br class="clear"/>';
			
			return $built;
		}
	};
	
	class Thread {
		public function __construct($id, $subject, $email, $name, $trip, $capcode, $body, $time, $thumb, $thumbx, $thumby, $file, $filex, $filey, $filesize, $filename, $ip, $sticky, $locked, $root=null, $mod=false, $hr=true) {
			global $config;
			if(!isset($root)) $root = $config['root'];
			
			$this->id = $id;
			$this->subject = utf8tohtml($subject);
			$this->email = $email;
			$this->name = utf8tohtml($name);
			$this->trip = $trip;
			$this->capcode = $capcode;
			$this->body = $body;
			$this->time = $time;
			$this->thumb = $thumb;
			$this->thumbx = $thumbx;
			$this->thumby = $thumby;
			$this->file = $file;
			$this->filex = $filex;
			$this->filey = $filey;
			$this->filesize = $filesize;
			$this->filename = $filename;
			$this->omitted = 0;
			$this->omitted_images = 0;
			$this->posts = Array();
			$this->ip = $ip;
			$this->sticky = $sticky;
			$this->locked = $locked;
			$this->root = $root;
			$this->mod = $mod;
			$this->hr = $hr;
			
			if($this->mod)
				// Fix internal links
				// Very complicated regex
				$this->body = preg_replace(
					'/<a(([a-zA-Z]+="[^"]+")|[a-zA-Z]+=[a-zA-Z]+|\s)*href="' . preg_quote($config['root'], '/') . '(' . sprintf(preg_quote($config['board_path'], '/'), '\w+') . ')/',
					'<a href="?/$3',
					$this->body
				);
		}
		public function link($pre = '') {
			global $config, $board;
			
			return $this->root . $board['dir'] . $config['dir']['res'] . $this->id . '.html' . '#' . $pre . $this->id;
		}
		public function add(Post $post) {
			$this->posts[] = $post;
		}
		public function postControls() {
			global $board, $config;
			
			$built = '';
			if($this->mod) {
				// Mod controls (on posts)
				$built .= '<span class="controls op">';
				
				// Delete
				if($this->mod['type'] >= $config['mod']['delete'])
					$built .= ' ' . confirmLink($config['mod']['link_delete'], 'Delete', 'Are you sure you want to delete this?', $board['uri'] . '/delete/' . $this->id);
				
				// Delete all posts by IP
				if($this->mod['type'] >= $config['mod']['deletebyip'])
					$built .= ' ' . confirmLink($config['mod']['link_deletebyip'], 'Delete all posts by IP', 'Are you sure you want to delete all posts by IP?', $board['uri'] . '/deletebyip/' . $this->id);
				
				// Ban
				if($this->mod['type'] >= $config['mod']['ban'])
					$built .= ' <a title="Ban" href="?/' . $board['uri'] . '/ban/' . $this->id . '">' . $config['mod']['link_ban'] . '</a>';
				
				// Ban & Delete
				if($this->mod['type'] >= $config['mod']['bandelete'])
					$built .= ' <a title="Ban & Delete" href="?/' . $board['uri'] . '/ban&amp;delete/' . $this->id . '">' . $config['mod']['link_bandelete'] . '</a>';
				
				// Stickies
				if($this->mod['type'] >= $config['mod']['sticky'])
					if($this->sticky)
						$built .= ' <a title="Make thread not sticky" href="?/' . $board['uri'] . '/unsticky/' . $this->id . '">' . $config['mod']['link_desticky'] . '</a>';
					else
						$built .= ' <a title="Make thread sticky" href="?/' . $board['uri'] . '/sticky/' . $this->id . '">' . $config['mod']['link_sticky'] . '</a>';
				
				// Lock
				if($this->mod['type'] >= $config['mod']['lock'])
					if($this->locked)
						$built .= ' <a title="Lock thread" href="?/' . $board['uri'] . '/unlock/' . $this->id . '">' . $config['mod']['link_unlock'] . '</a>';
					else
						$built .= ' <a title="Unlock thread" href="?/' . $board['uri'] . '/lock/' . $this->id . '">' . $config['mod']['link_lock'] . '</a>';
				
				
				$built .= '</span>';
			}
			return $built;
		}
		
		public function build($index=false) {
			global $board, $config;
			
			$built = '<p class="fileinfo">File: <a href="'	. $config['uri_img'] . $this->file .'">' . $this->file . '</a> <span class="unimportant">(' . 
			// Filesize
				format_bytes($this->filesize) .
			// File dimensions
			($this->filex && $this->filey ?
				', ' . $this->filex . 'x' . $this->filey
			: '' );
			// Aspect Ratio
			if($config['show_ratio'] && $this->filex && $this->filey) {
				$fraction = fraction($this->filex, $this->filey, ':');
				$built .= ', ' . $fraction;
			}
			// Filename
				$built .= ', ' . $this->filename . ')</span></p>' . 
			// Thumbnail
				'<a href="' . $config['uri_img'] . $this->file.'"><img src="' . $config['uri_thumb'] . $this->thumb.'" style="width:'.$this->thumbx.'px;height:'.$this->thumby.'px;" /></a>';
			
			$built .= '<div class="post op"><p class="intro"' . (!$index?' id="' . $this->id . '"':'') . '>';
			
			// Delete
			$built .= '<input type="checkbox" class="delete" name="delete_' . $this->id . '" id="delete_' . $this->id . '" /><label for="delete_' . $this->id . '">';
				
			// Subject
			if(!empty($this->subject))
				$built .= '<span class="subject">' . $this->subject . '</span> ';
			// Email
			if(!empty($this->email))
				$built .=  '<a class="email" href="mailto:' . $this->email . '">';
			// Name
			$built .= '<span class="name">' . $this->name . '</span>'
			// Trip
			. (!empty($this->trip) ? ' <span class="trip">'.$this->trip.'</span>':'')
			// End email
			. (!empty($this->email)? '</a>' : '')
			// Capcode
			. (!empty($this->capcode) ? capcode($this->capcode) : '');
			
			// IP Address
			if($this->mod && $this->mod['type'] >= $config['mod']['show_ip']) {
				$built .= ' [<a style="margin:0;" href="?/IP/' . $this->ip . '">' . $this->ip . '</a>]';
			}
			
			// Date/time
			$built .= ' ' . date($config['post_date'], $this->time);
			
			// End delete
			$built .= '</label>'
			
			// Poster ID
			. ($config['poster_ids'] ?
				' ID: ' . poster_id($this->ip, $this->id)
			: '')
			
			. ' <a class="post_no"' . 
			// JavaScript highlight
			($index?'':' onclick="highlightReply(' . $this->id . ');"') .
			' href="' . $this->link()  . '">No.</a>' . 
			// JavaScript cite
			'<a class="post_no"' . ($index?'':' onclick="citeReply(' . $this->id . ');"') . ' href="' . ($index ? $this->link('q') : 'javascript:void(0);') . '">'.$this->id.'</a>' .
			// Sticky
			($this->sticky ? '<img class="icon" title="Sticky" src="' . $config['image_sticky'] . '" />' : '') .
			// Locked
			($this->locked ? '<img class="icon" title="Locked" src="' . $config['image_locked'] . '" />' : '') .
			// [Reply]
			($index ? '<a href="' . $this->root . $board['dir'] . $config['dir']['res'] . $this->id . '.html">[Reply]</a>' : '') .
			
			// Mod controls
			$this->postControls() .
			'</p>';
			
			// Body
			$built .= '<p class="body">' . ($index ? truncate($this->body, $this->link()) : $this->body) . '</p>' .
			
			// Omitted posts
			($this->omitted || $this->omitted_images? '<span class="omitted">' .
				($this->omitted ?
					$this->omitted . ' post' . ($this->omitted==1?'':'s') .
						($this->omitted_images ? ' and ' : '')
				:'') .
				($this->omitted_images ?
					$this->omitted_images . ' image repl' . ($this->omitted_images==1?'y':'ies')
				:'') .
			' omitted. Click reply to view.</span>':'') .
			
			// End
			'</div>';
			
			// Replies
			foreach($this->posts as &$post) {
				$built .= $post->build($index);
			}
			
			$built .= '<br class="clear"/>' . ($this->hr ? '<hr/>' : '');
			return $built;
		}
	};
?>