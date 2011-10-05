<?php
	require 'info.php';
	
	function drudge_build($action, $settings) {
		// Possible values for $action:
		//	- all (rebuild everything, initialization)
		//	- news (news has been updated)
		//	- boards (board list changed)
		//	- post (a post has been made)
		
		$b = new Drudge();
		$b->build($action, $settings);
	}
	
	// Wrap functions in a class so they don't interfere with normal Tinyboard operations
	class Drudge {
		public function build($action, $settings) {
			global $config, $_theme, $threads;
			
			// Don't worry about this for now:
			//if($action == 'all') {
			//	copy($config['dir']['themes'] . '/' . $_theme . '/master.css', $config['dir']['home'] . 'drudge_master.css');
			//	copy($config['dir']['themes'] . '/' . $_theme . '/reset.css', $config['dir']['home'] . 'drudge_reset.css');
			//}
			
			$this->excluded = explode(' ', $settings['exclude']);
			
			if($action == 'all' || $action == 'post')
				file_write($config['dir']['home'] . 'landing/index.html', $this->homepage($settings));
		}
		
		private function spot($num) {
			global $config;
			
			$prime = $num < 7;
			
			if(!isset($this->threads[$num]))
				return '';
			
			$post = &$this->threads[$num];
			
			return ($prime ?
						'<img src="' . $config['uri_thumb'] . $post['thumb'] . '"/>'
					: '')
				. '<h2><a href="' . $post['email'] . '">' . $post['subject'] . '</a><a href="' . $post['email'] . '">...</a></h2><hr />';
		}
		
		// Build news page
		public function homepage($settings) {
			global $config, $board;
			
			openBoard('a');
			
			// HTML5
			$body = '<!DOCTYPE html><html>'
			. '<head>'
				//. '<link rel="stylesheet" media="screen" href="' . $config['url_stylesheet'] . '"/>'
				. '<link rel="stylesheet" media="screen" href="' . $config['root'] . 'landing/reset.css"/>'
				. '<link rel="stylesheet" media="screen" href="' . $config['root'] . 'landing/master.css"/>'
				. '<title>' . $settings['title'] . '</title>'
			. '</head><body>'
			
			// heading
			. '<div id="hed-container">'
			
				/*
					Sub-headlines related to the main headline appear here.
					They are pulled from the subject lines of the replies to the top thread.

					Drudge follows all stories with "...", other than the main headline
					We will use the ellipse to link to the forum thread, while the headline links directly to the story
				*/
			
				. '<div id="hed-sub">'
					. '<h2 class="sub"><a href="">Subject Line of latest reply in top thread</a><a href="">...</a></h2>'
				. '</div>'
				
				. '<div id="hed">'
					. '<img src="breitbart.jpg" title="" />'
					. '<br />';
			
			$this->threads = Array(); // 0 = main heading, 1-6 = prime spots, 7-18 = normal
			
			$query = query("SELECT *, `id` AS `thread_id`, (SELECT COUNT(*) FROM `posts_a` WHERE `thread` = `thread_id`) AS `replies` FROM `posts_a` WHERE `thread` IS NULL AND `email` != '' AND `subject` != '' ORDER BY `sticky` DESC, `replies` DESC, `bump` DESC LIMIT 19") or error(db_error());
			while($post = $query->fetch()) {
				$this->threads[] = $post;
			}
			
			// first prime gets headline
			$body .= '<h1><a href="' . $this->threads[0]['email'] . '">' . strtoupper($this->threads[0]['subject']) . '</a></h1>';
			
			$body .= '</div>'
			. '</div>'
			;
			
			$body .= '<div id="fold"><a href="http://serfin.us/a"><img src="serfinus.png" title="" /></a></div>';
			
			// begin three column layout here
			$body .= '<div id="below-the-fold">';
			
			
			// <stobor> Headline:   P     Left column:  xxPxPx      Center:  PxxxPx     Right: xPxxPx
			
			// first column
			$body .= '<div class="column" id="c-left">' .
					$this->spot(7) .
					$this->spot(8) .
					$this->spot(1) .
					$this->spot(9) .
					$this->spot(2) .
					$this->spot(10) .
				'</div>';
			
			// second column
			$body .= '<div class="column" id="c-center">' .
					$this->spot(3) .
					$this->spot(11) .
					$this->spot(12) .
					$this->spot(13) .
					$this->spot(4) .
					$this->spot(14) .
				'</div>';
			
			// third column
			$body .= '<div class="column" id="c-right">' .
					$this->spot(15) .
					$this->spot(5) .
					$this->spot(16) .
					$this->spot(17) .
					$this->spot(6) .
					$this->spot(18) .
				'</div>';
			
			
			// end container
			$body .= '</div>';
			
			// Finish page
			$body .= '<hr/><p class="unimportant" style="margin-top:20px;text-align:center;font-size:8pt;font-weight:normal">Powered by <a href="http://tinyboard.org/">Tinyboard</a></body></html>';
			
			return $body;
		}
	};
	
?>
