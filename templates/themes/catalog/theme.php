<?php
	require 'info.php';
	
	function catalog_build($action, $settings) {
		// Possible values for $action:
		//	- all (rebuild everything, initialization)
		//	- news (news has been updated)
		//	- boards (board list changed)
		//	- post (a post has been made)
		
		$b = new Catalog();
		$b->build($action, $settings);
	}
	
	// Wrap functions in a class so they don't interfere with normal Tinyboard operations
	class Catalog {
		public function build($action, $settings) {
			global $config, $_theme;
			
			if ($action == 'all') {
				copy('templates/themes/catalog/catalog.css', $config['dir']['home'] . $settings['css']);
			}
			
			$boards = explode(' ', $settings['boards']);
			foreach ($boards as $board) {
			if ($action == 'all' || $action == 'post')
				file_write($config['dir']['home'] . $board . '/catalog.html', $this->homepage($settings, $board));
			}
			
		}
		
		public function homepage($settings, $board_name) {
			global $config, $board;
			
			$recent_images = array();
			$recent_posts = array();
			$stats = array();
			
			$query = query(sprintf("SELECT *, `id` AS `thread_id`, (SELECT COUNT(*) FROM `posts_%s` WHERE `thread` = `thread_id`) AS `reply_count`, '%s' AS `board` FROM `posts_%s` WHERE `thread` IS NULL ORDER BY `bump` DESC", $board_name, $board_name, $board_name)) or error(db_error());
			
			while ($post = $query->fetch()) {
				openBoard($post['board']);
				
				$post['link'] = $config['root'] . $board['dir'] . $config['dir']['res'] . sprintf($config['file_page'], ($post['thread'] ? $post['thread'] : $post['id']));
				$post['board_name'] = $board['name'];
				$post['file'] = $config['uri_thumb'] . $post['thumb'];
				$recent_posts[] = $post;
			}
			
			
			return Element('themes/catalog/catalog.html', Array(
				'settings' => $settings,
				'config' => $config,
				'boardlist' => createBoardlist(),
				'recent_images' => $recent_images,
				'recent_posts' => $recent_posts,
				'stats' => $stats,
				'board' => $board_name,
				'link' => $config['root'] . $board['dir']
			));
		}
	};
	
?>
