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
		global $board, $mod;
		
		if(function_exists('sql_close')) sql_close();
		die(Element('page.html', Array(
			'index'=>ROOT,
			'title'=>'Error',
			'subtitle'=>'An error has occured.',
			'body'=>"<center>" .
			        "<h2>$message</h2>" .
				(isset($board) ? 
					"<p><a href=\"" . ROOT .
						($mod ? FILE_MOD . '?/' : '') .
						$board['dir'] . FILE_INDEX . "\">Go back</a>.</p>" : '').
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
		public function __construct($id, $thread, $subject, $email, $name, $trip, $body, $time, $thumb, $thumbx, $thumby, $file, $filex, $filey, $filesize, $filename, $ip, $root=ROOT, $mod=false) {
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
			$this->mod = $mod;
		}
		public function postControls() {
			global $board;
			
			$built = '';
			if($this->mod) {
				// Mod controls (on posts)
				$built .= '<span class="controls">';
				
				// Delete
				if($this->mod['type'] >= MOD_DELETE)
					$built .= ' <a title="Delete" href="?/' . $board['uri'] . '/delete/' . $this->id . '">' . MOD_LINK_DELETE . '</a>';
				
				// Delete all posts by IP
				if($this->mod['type'] >= MOD_DELETEBYIP)
					$built .= ' <a title="Delete all posts by IP" href="?/' . $board['uri'] . '/deletebyip/' . $this->id . '">' . MOD_LINK_DELETEBYIP . '</a>';
				
				// Ban
				if($this->mod['type'] >= MOD_BAN)
					$built .= ' <a title="Ban" href="?/' . $board['uri'] . '/ban/' . $this->id . '">' . MOD_LINK_BAN . '</a>';
				
				// Ban & Delete
				if($this->mod['type'] >= MOD_BANDELETE)
					$built .= ' <a title="Ban & Delete" href="?/' . $board['uri'] . '/ban&amp;delete/' . $this->id . '">' . MOD_LINK_BANDELETE . '</a>';
				
				// Delete file (keep post)
				if(!empty($this->file) && $this->mod['type'] >= MOD_DELETEFILE)
					$built .= ' <a title="Remove file" href="?/' . $board['uri'] . '/deletefile/' . $this->id . '">' . MOD_LINK_DELETEFILE . '</a>';
				
				$built .= '</span>';
			}
			return $built;
		}
		
		public function build($index=false) {
			global $board;
			
			$built =	'<div class="post reply"' . (!$index?' id="reply_' . $this->id . '"':'') . '>' . 
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
			$built .= '<span class="name">' . $this->name . '</span>'
			// Trip
			. (!empty($this->trip) ? ' <span class="trip">'.$this->trip.'</span>':'');
			
			// IP Address
			if($this->mod && $this->mod['type'] >= MOD_SHOW_IP) {
				$built .= ' [<a style="margin:0;" href="?/IP/' . $this->ip . '">' . $this->ip . '</a>]';
			}
			
			// End email
			if(!empty($this->email))
				$built .= '</a>';
			
			// Date/time
			$built .= ' ' . date(POST_DATE, $this->time);
			
			// End delete
			$built .= '</label>';
			
			$built .= ' <a class="post_no"' . 
			// JavaScript highlight
				($index?'':' onclick="highlightReply(' . $this->id . ');"') .
				' href="' . $this->root . $board['dir'] . DIR_RES . $this->thread . '.html' . '#' . $this->id . '">No.</a>' . 
			// JavaScript cite
				'<a class="post_no"' . ($index?'':' onclick="citeReply(' . $this->id . ');"') . ' href="' . ($index?$this->root . $board['dir'] . DIR_RES . $this->thread . '.html' . '#q' . $this->id:'javascript:void(0);') . '">'.$this->id.'</a>' . 
			'</p>';
		
			// File info
			if(!empty($this->file) && $this->file != 'deleted') {
				$built .= '<p class="fileinfo">File: <a href="'	. ROOT . $board['dir'] . DIR_IMG . $this->file .'">' . $this->file . '</a> <span class="unimportant">(' . 
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
				'<a href="' . ROOT . $board['dir'] . DIR_IMG . $this->file.'"><img src="' . ROOT . $board['dir'] . DIR_THUMB . $this->thumb.'" style="width:'.$this->thumbx.'px;height:'.$this->thumby.'px;" /></a>';
			} elseif($this->file == 'deleted') {
				$built .= '<img src="' . DELETED_IMAGE . '" />';
			}
			
			$built .= $this->postControls();
			
			// Body
			$built .= '<p class="body">' . $this->body . '</p></div><br class="clear"/>';
			
			return $built;
		}
	};
	
	class Thread {
		public $omitted = 0;
		public function __construct($id, $subject, $email, $name, $trip, $body, $time, $thumb, $thumbx, $thumby, $file, $filex, $filey, $filesize, $filename, $ip, $sticky, $locked, $root=ROOT, $mod=false) {
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
			$this->sticky = $sticky;
			$this->locked = $locked;
			$this->root = $root;
			$this->mod = $mod;
		}
		public function add(Post $post) {
			$this->posts[] = $post;
		}
		public function postControls() {
			global $board;
			
			$built = '';
			if($this->mod) {
				// Mod controls (on posts)
				$built .= '<span class="controls op">';
				
				// Delete
				if($this->mod['type'] >= MOD_DELETE)
					$built .= ' <a title="Delete" href="?/' . $board['uri'] . '/delete/' . $this->id . '">' . MOD_LINK_DELETE . '</a>';
				
				// Delete all posts by IP
				if($this->mod['type'] >= MOD_DELETEBYIP)
					$built .= ' <a title="Delete all posts by IP" href="?/' . $board['uri'] . '/deletebyip/' . $this->id . '">' . MOD_LINK_DELETEBYIP . '</a>';
				
				// Ban
				if($this->mod['type'] >= MOD_BAN)
					$built .= ' <a title="Ban" href="?/' . $board['uri'] . '/ban/' . $this->id . '">' . MOD_LINK_BAN . '</a>';
				
				// Ban & Delete
				if($this->mod['type'] >= MOD_BANDELETE)
					$built .= ' <a title="Ban & Delete" href="?/' . $board['uri'] . '/ban&amp;delete/' . $this->id . '">' . MOD_LINK_BANDELETE . '</a>';
				
				// Stickies
				if($this->mod['type'] >= MOD_STICKY)
					if($this->sticky)
						$built .= ' <a title="Make thread not sticky" href="?/' . $board['uri'] . '/unsticky/' . $this->id . '">' . MOD_LINK_DESTICKY . '</a>';
					else
						$built .= ' <a title="Make thread sticky" href="?/' . $board['uri'] . '/sticky/' . $this->id . '">' . MOD_LINK_STICKY . '</a>';
				
				// Lock
				if($this->mod['type'] >= MOD_LOCK)
					if($this->locked)
						$built .= ' <a title="Lock thread" href="?/' . $board['uri'] . '/unlock/' . $this->id . '">' . MOD_LINK_UNLOCK . '</a>';
					else
						$built .= ' <a title="Unlock thread" href="?/' . $board['uri'] . '/lock/' . $this->id . '">' . MOD_LINK_LOCK . '</a>';
				
				
				$built .= '</span>';
			}
			return $built;
		}
		
		public function build($index=false) {
			global $board;
			
			$built = '<p class="fileinfo">File: <a href="'	. ROOT . $board['dir'] . DIR_IMG . $this->file .'">' . $this->file . '</a> <span class="unimportant">(' . 
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
				'<a href="' . ROOT . $board['dir'] . DIR_IMG . $this->file.'"><img src="' . ROOT . $board['dir'] . DIR_THUMB . $this->thumb.'" style="width:'.$this->thumbx.'px;height:'.$this->thumby.'px;" /></a>';
			
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
			. (!empty($this->trip) ? ' <span class="trip">'.$this->trip.'</span>':'');
			
			// IP Address
			if($this->mod && $this->mod['type'] >= MOD_SHOW_IP) {
				$built .= ' [<a style="margin:0;" href="?/IP/' . $this->ip . '">' . $this->ip . '</a>]';
			}
			
			// End email
			if(!empty($this->email))
				$built .= '</a>';
			
			// Date/time
			$built .= ' ' . date(POST_DATE, $this->time);
			
			// End delete
			$built .= '</label>';
			
			$built .= ' <a class="post_no"' . 
			// JavaScript highlight
			($index?'':' onclick="highlightReply(' . $this->id . ');"') .
			' href="' . $this->root . $board['dir'] . DIR_RES . $this->id . '.html' . '#' . $this->id . '">No.</a>' . 
			// JavaScript cite
			'<a class="post_no"' . ($index?'':' onclick="citeReply(' . $this->id . ');"') . ' href="' . ($index?$this->root . $board['dir'] . DIR_RES . $this->id . '.html' . '#q' . $this->id:'javascript:void(0);') . '">'.$this->id.'</a>' .
			// Sticky
			($this->sticky ? '<img class="icon" title="Sticky" src="' . IMAGE_STICKY . '" />' : '') .
			// Locked
			($this->locked ? '<img class="icon" title="Locked" src="' . IMAGE_LOCKED . '" />' : '') .
			// [Reply]
			($index ? '<a href="' . $this->root . $board['dir'] . DIR_RES . $this->id . '.html">[Reply]</a>' : '') .
			
			// Mod controls
			$this->postControls() .
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