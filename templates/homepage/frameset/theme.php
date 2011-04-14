<?php
	$theme = Array();
	
	// Theme name
	$theme['name'] = 'Frameset';
	// Description (you can use Tinyboard markup here)
	$theme['description'] = 
'Use a basic frameset layout, with a list of boards and pages on a sidebar to the left of the page.

Users never have to leave the homepage; they can do all their browsing from the one page.';
	$theme['version'] = 'v0.1';
	
	// Theme configuration	
	$theme['config'] = Array();
	
	$theme['config'][] = Array(
		'title' => 'Title',
		'name' => 'title',
		'type' => 'text'
	);
	
	$theme['config'][] = Array(
		'title' => 'Slogan',
		'name' => 'subtitle',
		'type' => 'text'
	);
	
	// Unique function name for building everything
	$theme['build_function'] = 'frameset_build';
	
	if(!function_exists('frameset_build')) {
		function frameset_build($action, $settings) {
			// Possible values for $action:
			//	- all (rebuild everything, initialization)
			//	- news (news has been updated)
			//	- boards (board list changed)
			
			Frameset::build($action, $settings);
		}
	}
	
	// Wrap functions in a class so they don't interfere with normal Tinyboard operations
	class Frameset {
		public static function build($action, $settings) {
			global $config;
			
			if($action == 'all')
				file_put_contents($config['dir']['home'] . $config['file_index'], Frameset::homepage($settings));
			
			if($action == 'all' || $action == 'boards')
				file_put_contents($config['dir']['home'] . 'sidebar.html', Frameset::sidebar($settings));
			
			if($action == 'all' || $action == 'news')
				file_put_contents($config['dir']['home'] . 'news.html', Frameset::news($settings));
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
			. '</head><body>'
			// Sidebar
			. '<iframe src="sidebar.html" id="sidebar"></iframe>'
			// Main
			. '<iframe src="news.html" id="main"></iframe>'
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
			
			$body .= '<h1>' . $settings['title'] . '</h1>'
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
					'<span class="unimportant"> — by ' .
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
			'</ul></fieldset>';
			
			
			$body .= '<fieldset><legend>Boards</legend><ul>';
			
			$boards = listBoards();
			foreach($boards as &$_board) {
				openBoard($_board['uri']);
				$body .= '<li><a href="' .
					sprintf($config['board_path'], $board['uri']) .
				'">' . $board['name'] . '</a></li>';
			}
			
			$body .= '</ul></fieldset>';
			
			// Finish page
			$body .= '</body></html>';
			
			return $body;
		}
	};
	
?>