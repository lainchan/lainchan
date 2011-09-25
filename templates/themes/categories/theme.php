<?php
	require 'info.php';
	
	function categories_build($action, $settings) {
		// Possible values for $action:
		//	- all (rebuild everything, initialization)
		//	- news (news has been updated)
		//	- boards (board list changed)
		
		Categories::build($action, $settings);
	}

	// Wrap functions in a class so they don't interfere with normal Tinyboard operations
	class Categories {
		public static function build($action, $settings) {
			global $config;
			
			if($action == 'all')
				file_write($config['dir']['home'] . $config['file_index'], Categories::homepage($settings));
			
			if($action == 'all' || $action == 'boards')
				file_write($config['dir']['home'] . 'sidebar.html', Categories::sidebar($settings));
			
			if($action == 'all' || $action == 'news')
				file_write($config['dir']['home'] . 'news.html', Categories::news($settings));
		}
		
		// Build homepage
		public static function homepage($settings) {
			global $config;
			
			// HTML5
			return '<!DOCTYPE html><html>'
			. '<head>'
				. '<link rel="stylesheet" media="screen" href="' . $config['url_stylesheet'] . '"/>'
				. '<style type="text/css">'
					. 'iframe{border:none;margin:0;padding:0;height:99%;position:absolute}'
					. 'iframe#sidebar{left:0;top:0;width:15%}'
					. 'iframe#main{border-left:1px solid black;left:15%;top:0;width:85%}'
				. '</style>'
				. '<title>' . $settings['title'] . '</title>'
				. '<script type="text/javascript">'
					. 'function removeFrames() {'
					. 'window.location = document.getElementById("main").contentWindow.location.href'
					. '}'
				. '</script>'
			. '</head><body>'
			// Sidebar
			. '<iframe src="sidebar.html" id="sidebar" name="sidebar"></iframe>'
			// Main
			. '<iframe src="news.html" id="main" name="main"></iframe>'
			// Finish page
			. '</body></html>';
		}
		
		// Build news page
		public static function news($settings) {
			global $config;
			
			// HTML5
			$body = '<!DOCTYPE html><html>'
			. '<head>'
				. '<link rel="stylesheet" media="screen" href="' . $config['url_stylesheet'] . '"/>'
				. '<title>News</title>'
			. '</head><body>';
			
			$boardlist = createBoardlist();
			
			$body .= $boardlist['top']
				
			. '<h1>' . $settings['title'] . '</h1>'
				. '<div class="title">' . ($settings['subtitle'] ? utf8tohtml($settings['subtitle']) : '') . '</div>';
			
			$query = query("SELECT * FROM `news` ORDER BY `time` DESC") or error(db_error());
			if($query->rowCount() == 0) {
				$body .= '<p style="text-align:center" class="unimportant">(No news to show.)</p>';
			} else {
				// List news
				while($news = $query->fetch()) {
					$body .= '<div class="ban">' .
					'<h2 id="' . $news['id'] . '">' .
						($news['subject'] ?
							$news['subject']
						:
							'<em>no subject</em>'
						) .
					'<span class="unimportant"> &mdash; by ' .
						$news['name'] .
					' at ' .
						date($config['post_date'], $news['time']) .
					'</span></h2><p>' . $news['body'] . '</p></div>';
				}
			}
			
			// Finish page
			$body .= '</body></html>';
			
			return $body;
		}
		
		// Build sidebar
		public static function sidebar($settings) {
			global $config, $board;
			
			$body = '<!DOCTYPE html><html>'
			. '<head>' 
				. '<link rel="stylesheet" media="screen" href="' . $config['url_stylesheet'] . '"/>'
				. '<style type="text/css">'
					. 'fieldset{margin:10px 0}'
					. 'legend{width:100%;margin-left:-15px;background:#98E;border:1px solid white;color:white;font-weight:bold;padding:5px 5px}'
					. 'ul{margin:0;padding:0}'
					. 'li{list-style:none;padding:0 4px;margin: 0 4px}'
					. 'li a.system{font-weight:bold}'
				. '</style>'
				. '<base target="main" />'
				. '<title>' . $settings['title'] . '</title>'
			. '</head><body>';
			
			$body .= '<fieldset><legend>' . $settings['title'] . '</legend><ul>' .
				'<li><a class="system" href="news.html">[News]</a></li>' .
				'<li><a class="system" href="javascript:parent.removeFrames()">[Remove Frames]</a></li>' .
			'</ul></fieldset>';
			
			for($cat = 0; $cat < count($config['categories']); $cat++) {
				$body .= '<fieldset><legend>' . $config['categories'][$cat] . '</legend><ul>';
				
				foreach($config['boards'][$cat] as &$board) {
					$body .= '<li><a href="' .
						sprintf($config['board_path'], $board) .
					'">' . boardTitle($board) . '</a></li>';
				}
				
				$body .= '</ul></fieldset>';
			}
			
			foreach($config['custom_categories'] as $name => &$group) {
				$body .= '<fieldset><legend>' . $name . '</legend><ul>';
				
				foreach($group as $title => &$url) {
					$body .= '<li><a href="' . $url .
					'">' . $title . '</a></li>';
				}
				
				$body .= '</ul></fieldset>';
			}
			
			// Finish page
			$body .= '</body></html>';
			
			return $body;
		}
	};
	
?>
