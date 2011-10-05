<?php
	require 'info.php';
	
	$chon_settings = Array(
		'thumbwidth' => 80,
		'thumbheight' => 80
	);
	
	function chon_build($action, $settings) {
		$settings = Array(
			'title' => '4chon',
			'subtitle' => '',
			'boards' => Array(
				'new' => 'A place for the debate of political intrigue and current events. This board has a wide and varied user base with many unique, and sometimes controversial, views expressed.',
				'r9k' => 'A unique board for open discussion based on an original content script. If a post has been made before, the reposter is muted. The length of the mute  increases at the rate of 2^n seconds with each additional mute a user receives. Loosely based upon robot9000 of #xkcd-signal.',
				'v' => 'A board in which to discuss many of our favourite hobbies: video games. New and old, PC and console, the discussion is always fresh and on-topic.',
				'meta' => 'A board for the discussion and improvement of the community, 4chon or otherwise. Questions, comments, and modposts are frequently found here.'
			),
			'thumbwidth' => 80,
			'thumbheight' => 80
		);
		
		// Possible values for $action:
		//	- all (rebuild everything, initialization)
		//	- news (news has been updated)
		//	- boards (board list changed)
		
		Chon::build($action, $settings);
	}
	
	// Wrap functions in a class so they don't interfere with normal Tinyboard operations
	class Chon {
		public static function build($action, $settings) {
			global $config;
			
			//if($action == 'all' || $action == 'news')
				file_write($config['dir']['home'] . $config['file_index'], Chon::homepage($settings));
		}
		
		// Build news page
		public static function homepage($settings) {
			global $config, $board;
			
			// HTML5
			$body = '<!DOCTYPE html><html>'
			. '<head>'
				. '<link rel="stylesheet" media="screen" href="' . $config['url_stylesheet'] . '"/>'
				. '<link rel="stylesheet" media="screen" href="http://static.4chon.net/home.css"/>'
				. '<link rel="shortcut icon" href="http://static.4chon.net/favicon.gif" />'
				. '<script type="text/javascript" src="http://static.4chon.net/ga.js"></script>'
				. '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />'
				. '<meta name="keywords" content="4chon,chon,r9k,robot9000,r9k1,4chon r9k,4chon new,free speech imageboard,racist imageboard,4chan new,moot is a faggot,r9k deleted,new deleted,the vidya,robot 9000,tinyboard,imageboard" />'
				. '<title>' . $settings['title'] . '</title>'
			. '</head><body>';
			
			$body .= '<h1><a title="4chon" href="/">' . $settings['title'] . '</a></h1>'
				. '<div class="title">' . ($settings['subtitle'] ? utf8tohtml($settings['subtitle']) : '') . '</div>';
			
			$body .= '<div class="ban">';
			
			$body .= '<h2>4chon</h2><div class="wrap">';

			$body .= '<g:plusone size="medium" count="false" href="http://4chon.net/"></g:plusone>';

			$body .= '<h3>Welcome to 4chon.net!</h3>';
			$body .= '<p>The largest and most popular community based on <a href="http://tinyboard.org/">Tinyboard</a>; we\'re picking up where 4chan is dying! In mid-January, /r9k/ and /new/ were simultaneously deleted from 4chan. Less than an hour later, 4chon was created to give these boards a new life, and we have been growing ever since. With over one million posts in just a few months, we\'re the largest chon in existence.</p>';
			
			$body .= '<h3>Our Boards</h3>';
			$__boards = listBoards();
			foreach($settings['boards'] as $board => $description) {
				foreach($__boards as $_board) {
					if($_board['uri'] == $board) {
						$board = $_board;
						break;
					}
				}
				$body .= '<div class="board">';
				$body .= '<a title="' . $board['title'] . '" href="' . $config['root'] . $board['uri'] . '/' . $config['file_index'] . '" class="button">/' . $board['uri'] . '/</a>';
				$body .= '<p>' . $description . '</p>';
				$body .= '</div>';
			}
			
			$body .= '<div class="container"><div class="split">';
			
			$body .= '<div class="panel left">';
			
			
			$query = '';
			foreach($settings['boards'] as $board => $description) {
				$query .= sprintf("SELECT *, '%s' AS `board` FROM `posts_%s` WHERE `file` IS NOT NULL AND `file` != 'deleted' UNION ALL ", $board, $board);
			}
			$query = preg_replace('/UNION ALL $/', 'ORDER BY `time` DESC LIMIT 10', $query);
			$query = query($query) or error(db_error());
			
			$body .= '<div class="images">';
			while($post = $query->fetch()) {
				openBoard($post['board']);
				$x_ratio = $settings['thumbwidth'] / $post['thumbwidth'];
				$y_ratio = $settings['thumbheight'] / $post['thumbheight'];

				if(($post['thumbwidth'] <= $settings['thumbwidth']) && ($post['thumbheight'] <= $settings['thumbheight'])) {
					$tn_width = $post['thumbwidth'];
					$tn_height = $post['thumbheight'];
					} elseif (($x_ratio * $post['thumbheight']) < $settings['thumbheight']) {
						$tn_height = ceil($x_ratio * $post['thumbheight']);
						$tn_width = $settings['thumbwidth'];
					} else {
						$tn_width = ceil($y_ratio * $post['thumbwidth']);
						$tn_height = $settings['thumbheight'];
				}

				$post['thumbwidth'] = $tn_width;
				$post['thumbheight'] = $tn_height;
				
				$body .= '<a href="' . 
					$config['root'] . $board['dir'] . $config['dir']['res'] . ($post['thread']?$post['thread']:$post['id']) . '.html#' . $post['id'] .
				'"><img src="' . $config['uri_thumb'] . str_replace('.', '_small.', $post['thumb']) . '" style="width:' . $post['thumbwidth'] . 'px;height:' . $post['thumbheight'] . 'px;" /></a>';
			}
			$body .= '</div>';
			
			
			
			// Latest posts
			$body .= '<ul>';
			$query = '';
			foreach($settings['boards'] as $board => $description) {
				$query .= sprintf("SELECT *, '%s' AS `board` FROM `posts_%s` UNION ALL ", $board, $board);
			}
			$query = preg_replace('/UNION ALL $/', 'ORDER BY `time` DESC LIMIT 35', $query);
			$query = query($query) or error(db_error());
			
			while($post = $query->fetch()) {
				openBoard($post['board']);
				
				$body .= '<li><strong>' . $board['name'] . '</strong>: <a href="' . 
					$config['root'] . $board['dir'] . $config['dir']['res'] . ($post['thread']?$post['thread']:$post['id']) . '.html#' . $post['id'] .
				'">' . (empty($post['body']) ? '…' : pm_snippet($post['body'], 25)) . '</a></li>';
			}
			$body .= '</ul>';
			
			$body .= '</div>';
			
			$body .= '<div class="panel right">';
			$body .= '<h3>4chon Community</h3>';
			$body .= '<p>Aside from our boards, we also have an active community in the following places:</p>';
			
			$body .= '<ul>';
			$body .= '<li><a href="http://tv.4chon.net/">/tv/</a>  - We automatically maintain a list of livestreams that were posted on the boards, where users of 4chon can watch television shows, movies, and circlejerks.</li>';
			$body .= '<li>Minecraft - Our unofficial Minecraft server. Come build with bros and visit our giant penis sculpture! eironeia.datnode.net:24598</li>';
			$body .= '</ul>';
			
			$body .= '<h3>4chon</h3>';
			$body .= '<p>Please read the <a href="rules.html">general and board-specifc rules</a> as well as the <a href="faq.html">4chon FAQ</a> before posting.</p>';
			
			$body .= '<p>4chon keeps a <a href="stats.html">statistics page</a> which gives detailed information about all of our boards, such as posts per minute, user locations, referring sites and more!. There\'s also a <a href="http://status.4chon.net/map.html">map of our posters around the globe</a>!</p>';
			$body .= '<p>If, for any reason, you need to contact the 4chon staff, they can be reached in IRC - <a href="irc://irc.datnode.net/4chon">irc.datnode.net #4chon</a> / [<a href="irc.html">WebIRC</a>].</p>';
			
			$body .= '<p>The admin may be contacted at <a href="mailto:admin@4chon.net">admin@4chon.net</a> or <a href="/meta/">&gt;&gt;&gt;/meta/</a>.</p>';
			
			$body .= '<p>For status updates and explanations of downtime, please see our <a href="http://status.4chon.net/">status page</a> or follow us on <a href="http://twitter.com/#!/4chonable">our rarely-used @4chonable</a> Twitter account</p>';
			
			$body .= '</div>';
			
			$body .= '</div>';
			
			$body .= '</div></div></div>';
			
			$body .= '<p style="text-align:center">In memory of Scott &ldquo;Wingo&rdquo; Canner (1989-2011).</p>';
			
			$body .= '<ul style="text-align:center;list-style:none;padding:0">';
			
			// Total posts
			$query = 'SELECT SUM(`top`) AS `count` FROM (';
			foreach($settings['boards'] as $board => $description) {
				$query .= sprintf("SELECT MAX(`id`) AS `top` FROM `posts_%s` UNION ALL ", $board);
			}
			$query = preg_replace('/UNION ALL $/', ') AS `posts_all`', $query);
			$query = query($query) or error(db_error());
			$res = $query->fetch();
			$body .= '<li>Total posts: ' . number_format($res['count']) . '</li>';
			
			// Unique IPs
			$query = 'SELECT COUNT(DISTINCT(`ip`)) AS `count` FROM (';
			foreach($settings['boards'] as $board => $description) {
				$query .= sprintf("SELECT `ip` FROM `posts_%s` UNION ALL ", $board);
			}
			$query = preg_replace('/UNION ALL $/', ') AS `posts_all`', $query);
			$query = query($query) or error(db_error());
			$res = $query->fetch();
			$body .= '<li>Current posters: ' . number_format($res['count']) . '</li>';
			
			// Active content
			$query = 'SELECT SUM(`filesize`) AS `count` FROM (';
			foreach($settings['boards'] as $board => $description) {
				$query .= sprintf("SELECT `filesize` FROM `posts_%s` UNION ALL ", $board);
			}
			$query = preg_replace('/UNION ALL $/', ') AS `posts_all`', $query);
			$query = query($query) or error(db_error());
			$res = $query->fetch();
			$body .= '<li>Active content: ' . format_bytes($res['count']) . '</li>';
			
			$body .= '</ul>';
			
			// Finish page
			$body .= '<hr/><p class="unimportant" style="margin-top:20px;text-align:center;">Powered by <a href="http://tinyboard.org/">Tinyboard</a> | You must be at least 18 years of age to continue browsing.' .
			
			'<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>' . 
			
			'</body></html>';
			
			return $body;
		}
	};
	
?>
