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
			
			if($action == 'all' || $action == 'news' || $action == 'post')
				file_write($config['dir']['home'] . 'news.html', Categories::news($settings));
		}
		
		// Build homepage
		public static function homepage($settings) {
			global $config;
			
			// HTML5
			return '<!DOCTYPE html><html>'
			. '<head>'
				. '<meta charset="utf-8">'
				. '<link rel="stylesheet" media="screen" href="' . $config['url_stylesheet'] . '"/>'
				. '<style type="text/css">'
					. 'iframe{border:none;margin:0;padding:0;height:100%;position:absolute}'
					. 'iframe#sidebar{left:0;top:0;width:15%}'
					. 'iframe#main{left:15%;top:0;width:85%}'
				. '</style>'
				. '<title>' . $settings['title'] . '</title>'
				. '<script type="text/javascript">'
					. 'function removeFrames() {'
					. 'window.location = document.getElementById("main").contentWindow.location.href;'
					. '}'
					. 'function globalChangeStyle(x)'
					. '{'
					. 'document.getElementById("main").contentWindow.changeStyle(x);'
					. 'document.getElementById("sidebar").contentWindow.changeStyle(x);'
					. '}'
					. 'function goNews() {'
					. 'window.location = \'/\';'
					. '}'
					. 'window.f = true;'
				. '</script>'
				. '<meta name="description" content="A fansite and imageboard revolving around the cult classic PC game Yume Nikki." />'
				. '<meta name="keywords" content="yume nikki, imageboard, boards, forums, uboa, madotsuki, poniko, seccom masada, monoko, monoe, images, discussion, video games" />'
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
			global $config, $board;
			
			// HTML5
			$body = '<!DOCTYPE html><html>'
			. '<head>'
				. '<meta charset="utf-8">'
				. '<link rel="stylesheet" href="frontpage.css" />'
				. '<link rel="stylesheet" media="screen" id="stylesheet" href="/stylesheets/style.css">'
				. '<script type="text/javascript" src="/styleswitch-sidebar.js"></script>'
				. '<title>' . $settings['title'] . ' - News</title>'
			. '</head><body>';
			
			$boardlist = createBoardlist();
			
			$body .= $boardlist['top']
			. '<br />';
			
			$boards = listBoards();

			$body .= '<div id="maintable"><div id="logo"></div><div id="announcement">Don\'t touch the lights!</div><table style="margin-bottom: 4px; width: 100%;"><tr>';
			// Recent Posts
			
			$body .= '<td style="width: 100%;"><div class="post_wrap"><div class="post_header"><b>Recent Posts</b></div><div class="post_body"><div class="post_content" style="padding-bottom: 10px;">';
			
				$query = '';
				foreach($boards as &$_board) {
				    // Block Board
				    if ($_board['uri'] != "aurora") {
					    $query .= sprintf("SELECT *, '%s' AS `board` FROM `posts_%s` UNION ALL ", $_board['uri'], $_board['uri']);
					}
				}
				$query = preg_replace('/UNION ALL $/', 'ORDER BY `time` DESC LIMIT 15', $query);
				$query = query($query) or error(db_error());
				
				while($post = $query->fetch()) {
					openBoard($post['board']);
					
					$body .= '<strong>' . $board['name'] . '</strong>: <a href="' . 
						$config['root'] . $board['dir'] . $config['dir']['res'] . ($post['thread']?$post['thread']:$post['id']) . '.html#' . $post['id'] .
					'">';
					$snip = pm_snippet($post['body'], 95);
					if($snip === "<em></em>")
					{
						$body .= '&lt;empty&gt;';
					}
					else
					{
						$body .= $snip;
					}
					$body .= '</a><br />';
				}
			
			// News
			
			$body .= '</div></div></div></td></tr></table>';
			
			$query = query("SELECT * FROM `news` ORDER BY `time` DESC LIMIT 5") or error(db_error());
			if($query->rowCount() == 0) {
				$body .= '<p style="text-align:center" class="unimportant">(No news to show.)</p>';
			} else {
				// List news
				while($news = $query->fetch()) {
					$body .= '<div class="post_wrap"><div class="post_header">' .
						// Newer than 5 days?
						(time() - $news['time'] <= 432000 ?
							'<em><b><span style="color: #D03030;">*NEW*</span></b></em> '
						:
							''
						) .
						($news['subject'] ?
							$news['subject']
						:
							'<em>no subject</em>'
						) .
					' &mdash; by ' .
						$news['name'] .
					' at ' .
						strftime($config['post_date'], $news['time']) .
					'</div><div class="post_body"><div class="post_content">' .
					$news['body'] .
					'</div></div></div>';
				}
			}
			
			// Finish page
			$body .= '<br />';
			$body .= '</div></body></html>';
			
			return $body;
		}
		
		// Build sidebar
		public static function sidebar($settings) {
			global $config, $board;
			
			$body = '<!DOCTYPE html><html>'
			. '<head>' 
				. '<meta charset="utf-8">'
				. '<link rel="stylesheet" media="screen" href="' . $config['url_stylesheet'] . '"/>'
				. '<style type="text/css">'
					. 'fieldset{margin:10px 0}'
					. 'legend{width:100%;margin-left:-15px;border:1px solid white;color:white;font-weight:bold;padding:5px 5px}'
					. 'ul{margin:0;padding:0}'
					. 'li{list-style:none;padding:0 4px;margin: 0 4px}'
					. 'li a.system{font-weight:bold}'
				. '</style>'
				. '<link rel="stylesheet" type="text/css" id="stylesheet" href="/stylesheets/uboachan.css">'
				. '<script type="text/javascript" src="/styleswitch-sidebar.js"></script>'
				. '<base target="main" />'
				. '<title>' . $settings['title'] . '</title>'
			. '</head><body>';
			
			$body .= '<fieldset><legend class="category">' . $settings['title'] . '</legend><ul>' .
				'<li><a class="system" href="news.html">[News]</a></li>' .
				'<li><a class="system" href="rules.php">[Rules]</a></li>' .
				'<li><a class="system" href="faq.php">[FAQ]</a></li>' .
				'<li><a class="system" href="search.php">[Search]</a></li>' .
				'<li><a class="system" href="http://archive.uboachan.net/">[Archive]</a></li>' .
				'<li><a class="system" href="http://archive.uboachan.net/media/src/Yume_Nikki.rar">[Download v0.10]</a></li>' .
				'<li><a class="system" href="javascript:parent.removeFrames()">[Remove Frames]</a></li>' .
				'<li><a href="javascript:changeStyleForAll(\'YB\');">[YB]</a> <a href="javascript:changeStyleForAll(\'Y\');">[Y]</a> <a href="javascript:changeStyleForAll(\'U\');">[U]</a> <a href="javascript:changeStyleForAll(\'UG\');">[UG]</a> <a href="javascript:changeStyleForAll(\'RZ\');">[RZ]</a></li>'.
			'</ul></fieldset>';
			
			for($cat = 0; $cat < count($config['categories']); $cat++) {
				$body .= '<fieldset><legend class="category">' . $config['categories'][$cat] . '</legend><ul>';
				
				foreach($config['boards'][$cat] as &$board) {
					$body .= '<li><a href="' .
						sprintf($config['board_path'], $board) .
					'">' . boardTitle($board) . '</a></li>';
				}
				
				$body .= '</ul></fieldset>';
			}
			
			foreach($config['custom_categories'] as $name => &$group) {
				$body .= '<fieldset><legend class="category">' . $name . '</legend><ul>';
				
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
