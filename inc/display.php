<?php
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

	function error($message) {
		global $board;
		
		if(function_exists('sql_close')) sql_close();
		die(Element('page.html', Array(
			'index'=>ROOT,
			'title'=>'Error',
			'subtitle'=>'An error has occured.',
			'body'=>"<center>" .
			        "<h2>$message</h2>" .
				(isset($board) ? "<p><a href=\"" . ROOT . $board['dir'] . FILE_INDEX . "\">Go back</a>.</p>" : '').
			        "</center>"
		)));
	}
	
	function loginForm($error=false, $username=false) {
		if(function_exists('sql_close')) sql_close();
		die(Element('page.html', Array(
			'index'=>ROOT,
			'title'=>'Login',
			'body'=>Element('login.html', Array(
				'index'=>ROOT,
				'error'=>$error,
				'username'=>$username
				)
			)
		)));
	}
	
	class Post {
		public function __construct($id, $thread, $subject, $email, $name, $trip, $body, $time, $thumb, $thumbx, $thumby, $file, $filex, $filey, $filesize, $filename, $ip, $root=ROOT) {
			$this->id = $id;
			$this->thread = $thread;
			$this->subject = utf8tohtml($subject);
			$this->email = $email;
			$this->name = utf8tohtml($name);
			$this->trip = $trip;
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
		}
		public function build($index=false) {
			global $board, $mod;
			
			$built =	'<div class="post reply"' . (!$index?' id="reply_' . $this->id . '"':'') . '>' . 
						'<p class="intro"' . (!$index?' id="' . $this->id . '"':'') . '>';
			
			// Subject
			if(!empty($this->subject))
				$built .= '<span class="subject">' . $this->subject . '</span> ';
			// Email
			if(!empty($this->email))
				$built .=  '<a class="email" href="mailto:' . $this->email . '">';
			// Name
			$built .= '<span class="name">' . $this->name . '</span>'
			// Trip
			. (!empty($this->trip) ? ' <span class="trip">'.$this->trip.'</span>':'');
			
			// IP Address
			if($mod && $mod['type'] >= MOD_SHOW_IP) {
				$built .= ' [<a style="margin:0;" href="?/IP/' . $this->ip . '">' . $this->ip . '</a>]';
			}
			
			// End email
			if(!empty($this->email))
				$built .= '</a>';
			
			// Date/time
			$built .= ' ' . date('m/d/y (D) H:i:s', $this->time);
			
			$built .= ' <a class="post_no"' . 
			// JavaScript highlight
				($index?'':' onclick="highlightReply(' . $this->id . ');"') .
				' href="' . $this->root . $board['dir'] . DIR_RES . $this->thread . '.html' . '#' . $this->id . '">No.</a>' . 
			// JavaScript cite
				'<a class="post_no"' . ($index?'':'onclick="citeReply(' . $this->id . ');"') . 'href="' . ($index?$this->root . DIR_RES . $this->thread . '.html' . '#q' . $this->id:'javascript:void(0);') . '">'.$this->id.'</a>' . 
			'</p>';
		
			// File info
			if(!empty($this->file)) {
				$built .= '<p class="fileinfo">File: <a href="'	. $this->root . $board['dir'] . DIR_IMG . $this->file .'">' . $this->file . '</a> <span class="unimportant">(' . 
			// Filesize
					format_bytes($this->filesize) . ', ' . 
			// File dimensions
					$this->filex . 'x' . $this->filey;
			// Aspect Ratio
				if(SHOW_RATIO) {
					$fraction = fraction($this->filex, $this->filey, ':');
					$built .= ', ' . $fraction;
				}
			// Filename
				$built .= ', ' . $this->filename . ')</span></p>' . 
			// Thumbnail
					'<a href="' . $this->root . $board['dir'] . DIR_IMG . $this->file.'"><img src="' . $this->root . $board['dir'] . DIR_THUMB . $this->thumb.'" style="width:'.$this->thumbx.'px;height:'.$this->thumby.'px;" /></a>';
			}
			
			// Body
			$built .= '<p class="body">' . $this->body . '</p></div><br class="clear"/>';
			
			return $built;
		}
	};
	
	class Thread {
		public $omitted = 0;
		public function __construct($id, $subject, $email, $name, $trip, $body, $time, $thumb, $thumbx, $thumby, $file, $filex, $filey, $filesize, $filename, $ip, $root=ROOT) {
			$this->id = $id;
			$this->subject = utf8tohtml($subject);
			$this->email = $email;
			$this->name = utf8tohtml($name);
			$this->trip = $trip;
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
			$this->posts = Array();
			$this->ip = $ip;
			$this->root = $root;
		}
		public function add(Post $post) {
			$this->posts[] = $post;
		}
		
		
		public function build($index=false) {
			global $board, $mod;
			
			$built = '<p class="fileinfo">File: <a href="'	. $this->root . $board['dir'] . DIR_IMG . $this->file .'">' . $this->file . '</a> <span class="unimportant">(' . 
			// Filesize
				format_bytes($this->filesize) . ', ' . 
			// File dimensions
				$this->filex . 'x' . $this->filey;
			// Aspect Ratio
			if(SHOW_RATIO) {
				$fraction = fraction($this->filex, $this->filey, ':');
				$built .= ', ' . $fraction;
			}
			// Filename
				$built .= ', ' . $this->filename . ')</span></p>' . 
			// Thumbnail
				'<a href="' . $this->root . $board['dir'] . DIR_IMG . $this->file.'"><img src="' . $this->root . $board['dir'] . DIR_THUMB . $this->thumb.'" style="width:'.$this->thumbx.'px;height:'.$this->thumby.'px;" /></a>';
			
			$built .= '<div class="post op"><p class="intro"' . (!$index?' id="' . $this->id . '"':'') . '>';
			
			// Subject
			if(!empty($this->subject))
				$built .= '<span class="subject">' . $this->subject . '</span> ';
			// Email
			if(!empty($this->email))
				$built .=  '<a class="email" href="mailto:' . $this->email . '">';
			// Name
			$built .= '<span class="name">' . $this->name . '</span>'
			// Trip
			. (!empty($this->trip) ? ' <span class="trip">'.$this->trip.'</span>':'');
			
			// IP Address
			if($mod && $mod['type'] >= MOD_SHOW_IP) {
				$built .= ' [<a style="margin:0;" href="?/IP/' . $this->ip . '">' . $this->ip . '</a>]';
			}
			
			// End email
			if(!empty($this->email))
				$built .= '</a>';
			
			// Date/time
			$built .= ' ' . date('m/d/y (D) H:i:s', $this->time);
			
			$built .= ' <a class="post_no"' . 
			// JavaScript highlight
			($index?'':' onclick="highlightReply(' . $this->id . ');"') .
			' href="' . $this->root . $board['dir'] . DIR_RES . $this->id . '.html' . '#' . $this->id . '">No.</a>' . 
			// JavaScript cite
			'<a class="post_no"' . ($index?'':'onclick="citeReply(' . $this->id . ');"') . 'href="' . ($index?$this->root . $board['dir'] . DIR_RES . $this->id . '.html' . '#q' . $this->id:'javascript:void(0);') . '">'.$this->id.'</a>' .
			// [Reply]
			($index ? '<a href="' . $this->root . $board['dir'] . DIR_RES . $this->id . '.html">[Reply]</a>' : '') .
			'</p>';
		
			// Body
			$built .= $this->body .
			
			// Omitted posts
			($this->omitted ? '<span class="omitted">' . $this->omitted . ' post' . ($this->omitted==1?'':'s') . ' omitted. Click reply to view.</span>':'') .
			
			// End
			'</div>';
			
			// Replies
			foreach($this->posts as &$post) {
				$built .= $post->build($index);
			}
			
			$built .= '<br class="clear"/><hr/>';
			return $built;
		}
	};
?>