<?php
	require 'info.php';
	
	function recentposts_build($action, $settings) {
		// Possible values for $action:
		//	- all (rebuild everything, initialization)
		//	- news (news has been updated)
		//	- boards (board list changed)
		//	- post (a post has been made)
		
		$b = new RecentPosts();
		$b->build($action, $settings);
	}
	
	// Wrap functions in a class so they don't interfere with normal Tinyboard operations
	class RecentPosts {
		public function build($action, $settings) {
			global $config, $_theme;
			
			if ($action == 'all') {
				copy('templates/themes/recent/' . $settings['basecss'], $config['dir']['home'] . $settings['css']);
			}
			
			$this->excluded = explode(' ', $settings['exclude']);
			
			if ($action == 'all' || $action == 'post')
				file_write($config['dir']['home'] . $settings['html'], $this->homepage($settings));
		}
		
		// Build news page
		public function homepage($settings) {
			global $config, $board;
			
			$recent_images = Array();
			$recent_posts = Array();
			$stats = Array();
			
			$boards = listBoards();
			
			$query = '';
			foreach ($boards as &$_board) {
				if (in_array($_board['uri'], $this->excluded))
					continue;
				$query .= sprintf("SELECT *, '%s' AS `board` FROM `posts_%s` WHERE `file` IS NOT NULL AND `file` != 'deleted' AND `thumb` != 'spoiler' UNION ALL ", $_board['uri'], $_board['uri']);
			}
			$query = preg_replace('/UNION ALL $/', 'ORDER BY `time` DESC LIMIT ' . (int)$settings['limit_images'], $query);
			$query = query($query) or error(db_error());
			
			while ($post = $query->fetch()) {
				openBoard($post['board']);
				
				// board settings won't be available in the template file, so generate links now
				$post['link'] = $config['root'] . $board['dir'] . $config['dir']['res'] . sprintf($config['file_page'], ($post['thread'] ? $post['thread'] : $post['id'])) . '#' . $post['id'];
				$post['src'] = $config['uri_thumb'] . $post['thumb'];
				
				$recent_images[] = $post;
			}
			
			
			$query = '';
			foreach ($boards as &$_board) {
				if (in_array($_board['uri'], $this->excluded))
					continue;
				$query .= sprintf("SELECT *, '%s' AS `board` FROM `posts_%s` UNION ALL ", $_board['uri'], $_board['uri']);
			}
			$query = preg_replace('/UNION ALL $/', 'ORDER BY `time` DESC LIMIT ' . (int)$settings['limit_posts'], $query);
			$query = query($query) or error(db_error());
			
			while ($post = $query->fetch()) {
				openBoard($post['board']);
				
				$post['link'] = $config['root'] . $board['dir'] . $config['dir']['res'] . sprintf($config['file_page'], ($post['thread'] ? $post['thread'] : $post['id'])) . '#' . $post['id'];
				$post['snippet'] = pm_snippet($post['body'], 30);
				$post['board_name'] = $board['name'];
				
				$recent_posts[] = $post;
			}
			
			// Total posts
			$query = 'SELECT SUM(`top`) AS `count` FROM (';
			foreach ($boards as &$_board) {
				if (in_array($_board['uri'], $this->excluded))
					continue;
				$query .= sprintf("SELECT MAX(`id`) AS `top` FROM `posts_%s` UNION ALL ", $_board['uri']);
			}
			$query = preg_replace('/UNION ALL $/', ') AS `posts_all`', $query);
			$query = query($query) or error(db_error());
			$res = $query->fetch();
			$stats['total_posts'] = number_format($res['count']);
			
			// Unique IPs
			$query = 'SELECT COUNT(DISTINCT(`ip`)) AS `count` FROM (';
			foreach ($boards as &$_board) {
				if (in_array($_board['uri'], $this->excluded))
					continue;
				$query .= sprintf("SELECT `ip` FROM `posts_%s` UNION ALL ", $_board['uri']);
			}
			$query = preg_replace('/UNION ALL $/', ') AS `posts_all`', $query);
			$query = query($query) or error(db_error());
			$res = $query->fetch();
			$stats['unique_posters'] = number_format($res['count']);
			
			// Active content
			$query = 'SELECT SUM(`filesize`) AS `count` FROM (';
			foreach ($boards as &$_board) {
				if (in_array($_board['uri'], $this->excluded))
					continue;
				$query .= sprintf("SELECT `filesize` FROM `posts_%s` UNION ALL ", $_board['uri']);
			}
			$query = preg_replace('/UNION ALL $/', ') AS `posts_all`', $query);
			$query = query($query) or error(db_error());
			$res = $query->fetch();
			$stats['active_content'] = $res['count'];
			
			return Element('themes/recent/recent.html', Array(
				'settings' => $settings,
				'config' => $config,
				'boardlist' => createBoardlist(),
				'recent_images' => $recent_images,
				'recent_posts' => $recent_posts,
				'stats' => $stats
			));
		}
	};
	
?>
