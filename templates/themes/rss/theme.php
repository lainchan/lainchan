<?php
	require 'info.php';
	
	function rss_recentposts_build($action, $settings, $board) {
		// Possible values for $action:
		//	- all (rebuild everything, initialization)
		//	- news (news has been updated)
		//	- boards (board list changed)
		//	- post (a post has been made)
		//	- post-thread (a thread has been made)
		
		$b = new RSSRecentPosts();
		$b->build($action, $settings);
	}
	
	// Wrap functions in a class so they don't interfere with normal Tinyboard operations
	class RSSRecentPosts {
		public function build($action, $settings) {
			global $config, $_theme,$board;
			
			
			$this->excluded = explode(' ', $settings['exclude']);
			if ($settings['show_threads_only'] == 'true'){
				if ($action == 'all' || $action == 'post-thread' || $action == 'post-delete')
					file_write($config['dir']['home'] . $settings['xml'], $this->site_rsspage($settings));
			}
			else {	
				if ($action == 'all' || $action == 'post' || $action == 'post-thread' || $action == 'post-delete')
					file_write($config['dir']['home'] . $settings['xml'], $this->site_rsspage($settings));
			}

			if ($settings['enable_per_board'] == 'true'){
				$boards = listBoards();
				foreach ($boards as &$_board) {
					if (in_array($_board['uri'], $this->excluded))
						continue;

					if ($settings['show_threads_only'] == 'true'){
						if ($action == 'all' || $action == 'post-thread' || $action == 'post-delete'){
							if (in_array($_board,array_keys($config['overboards']))){
							file_write($config['dir']['home']  . $board['dir'] . $settings['xml'], $this->overboard_rsspage($settings,$_board));
							
							}
							else {
							openBoard($_board['uri']);
							file_write($config['dir']['home']  . $board['dir'] . $settings['xml'], $this->board_rsspage($settings,$_board));		       
							}
						}
					}
					else {	
						if ($action == 'all' || $action == 'post' || $action == 'post-thread' || $action == 'post-delete') {
							if (in_array($_board,array_keys($config['overboards']))){
							file_write($config['dir']['home']  . $board['dir'] . $settings['xml'], $this->overboard_rsspage($settings,$_board));
							
							}
							else {
							openBoard($_board['uri']);
							file_write($config['dir']['home']  . $board['dir'] . $settings['xml'], $this->board_rsspage($settings,$_board));
							}
						}
					}
				}

			}
		}
		
		// Build news page
		public function site_rsspage($settings) {
			global $config, $board;
			
			$recent_posts = Array();
			
			$boards = listBoards();
			
			$query = '';
			foreach ($boards as &$_board) {
				if (in_array($_board['uri'], $this->excluded))
					continue;
				if ($settings['show_threads_only'] == 'true'){
					$query .= sprintf("SELECT *, '%s' AS `board` FROM ``posts_%s`` WHERE `thread` is null UNION ALL ", $_board['uri'], $_board['uri']);
				}
				else {
					$query .= sprintf("SELECT *, '%s' AS `board` FROM ``posts_%s`` UNION ALL ", $_board['uri'], $_board['uri']);
				}
			}
			$query = preg_replace('/UNION ALL $/', 'ORDER BY `time` DESC LIMIT ' . (int)$settings['limit_posts'], $query);
			$query = query($query) or error(db_error());
			
			while ($post = $query->fetch(PDO::FETCH_ASSOC)) {
				openBoard($post['board']);
				
				$post['link'] = $config['root'] . $board['dir'] . $config['dir']['res'] . sprintf($config['file_page'], ($post['thread'] ? $post['thread'] : $post['id'])) . '#' . $post['id'];
				$post['snippet'] = pm_snippet($post['body'], 30);
				$post['board_name'] = $board['name'];
				
				$recent_posts[] = $post;
			}
			
			
			return Element('themes/rss/rss.xml', Array(
				'settings' => $settings,
				'config' => $config,
				'recent_posts' => $recent_posts,
			));
		}
		
		// Build news page
		public function board_rsspage($settings,$_board) {
			global $config, $board;
			
			$recent_posts = Array();
			
			$query = '';
			if ($settings['show_threads_only'] == 'true'){
				$query .= sprintf("SELECT *, '%s' AS `board` FROM ``posts_%s`` WHERE `thread` is null UNION ALL ", $_board['uri'], $_board['uri']);
			}
			else {
				$query .= sprintf("SELECT *, '%s' AS `board` FROM ``posts_%s`` UNION ALL ", $_board['uri'], $_board['uri']);
			}
			$query = preg_replace('/UNION ALL $/', 'ORDER BY `time` DESC LIMIT ' . (int)$settings['limit_posts'], $query);
			$query = query($query) or error(db_error());
			
			while ($post = $query->fetch(PDO::FETCH_ASSOC)) {
				openBoard($post['board']);
				
				$post['link'] = $config['root'] . $board['dir'] . $config['dir']['res'] . sprintf($config['file_page'], ($post['thread'] ? $post['thread'] : $post['id'])) . '#' . $post['id'];
				$post['snippet'] = pm_snippet($post['body'], 30);
				$post['board_name'] = $board['name'];
				
				$recent_posts[] = $post;
			}
			
			
			return Element('themes/rss/rss.xml', Array(
				'settings' => $settings,
				'config' => $config,
				'recent_posts' => $recent_posts,
			));
		}
		
		// Build news page
		public function overboard_rsspage($settings,$overboard) {
			global $config, $board;
			
			$recent_posts = Array();
			
			$boards = listBoards();
			
			$query = '';
			foreach ($boards as &$_board) {
				if (array_key_exists('exclude',$config['overboards'][$overboard])) {
					if (in_array($_board['uri'],$config['overboards'][$overboard]['exclude'] ))
						continue;
					if ($settings['show_threads_only'] == 'true'){
						$query .= sprintf("SELECT *, '%s' AS `board` FROM ``posts_%s`` WHERE `thread` is null UNION ALL ", $_board['uri'], $_board['uri']);
					}
					else {
						$query .= sprintf("SELECT *, '%s' AS `board` FROM ``posts_%s`` UNION ALL ", $_board['uri'], $_board['uri']);
					}
				}
				elseif (array_key_exists('include',$config['overboards'][$overboard])) {
					if (in_array($_board['uri'],$config['overboards'][$overboard]['include'] )){
					if ($settings['show_threads_only'] == 'true'){
						$query .= sprintf("SELECT *, '%s' AS `board` FROM ``posts_%s`` WHERE `thread` is null UNION ALL ", $_board['uri'], $_board['uri']);
					}
					else {
						$query .= sprintf("SELECT *, '%s' AS `board` FROM ``posts_%s`` UNION ALL ", $_board['uri'], $_board['uri']);
					}
					
					}
				
				}

			}
			$query = preg_replace('/UNION ALL $/', 'ORDER BY `time` DESC LIMIT ' . (int)$settings['limit_posts'], $query);
			$query = query($query) or error(db_error());
			
			while ($post = $query->fetch(PDO::FETCH_ASSOC)) {
				openBoard($post['board']);
				
				$post['link'] = $config['root'] . $post['board'] . $config['dir']['res'] . sprintf($config['file_page'], ($post['thread'] ? $post['thread'] : $post['id'])) . '#' . $post['id'];
				$post['snippet'] = pm_snippet($post['body'], 30);
				$post['board_name'] = $post['board'];
				
				$recent_posts[] = $post;
			}
			
			
			return Element('themes/rss/rss.xml', Array(
				'settings' => $settings,
				'config' => $config,
				'recent_posts' => $recent_posts,
			));
		}
		
	};
	
?>
