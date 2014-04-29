<?php
	require 'info.php';
	
	function catalog_build($action, $settings, $board) {
		global $config;
		
		// Possible values for $action:
		//	- all (rebuild everything, initialization)
		//	- news (news has been updated)
		//	- boards (board list changed)
		//	- post (a reply has been made)
		//	- post-thread (a thread has been made)
		
		$boards = explode(' ', $settings['boards']);
				
		if ($action == 'all') {
			foreach ($boards as $board) {
				$b = new Catalog();
				$b->build($settings, $board);
			}
		} elseif ($action == 'post-thread' || ($settings['update_on_posts'] && $action == 'post') || ($settings['update_on_posts'] && $action == 'post-delete') && in_array($board, $boards)) {
			$b = new Catalog();
			$b->build($settings, $board);
		}
	}
	
	// Wrap functions in a class so they don't interfere with normal Tinyboard operations
	class Catalog {
		public function build($settings, $board_name) {
			global $config, $board;
			
			openBoard($board_name);
			
			$recent_images = array();
			$recent_posts = array();
			$stats = array();
			
                        $query = query(sprintf("SELECT *, `id` AS `thread_id`, (SELECT COUNT(*) FROM ``posts_%s`` WHERE `thread` = `thread_id`) AS `reply_count`, (SELECT COUNT(*) FROM ``posts_%s`` WHERE `thread` = `thread_id` AND `filehash` IS NOT NULL) AS `image_count`, (SELECT `time` FROM ``posts_%s`` WHERE `thread` = `thread_id` ORDER BY `time` DESC LIMIT 1) AS `last_reply`, (SELECT `name` FROM ``posts_%s`` WHERE `thread` = `thread_id` ORDER BY `time` DESC LIMIT 1) AS `last_reply_name`, (SELECT `subject` FROM ``posts_%s`` WHERE `thread` = `thread_id` ORDER BY `time` DESC LIMIT 1) AS `last_reply_subject`, '%s' AS `board` FROM ``posts_%s`` WHERE `thread`  IS NULL ORDER BY `bump` DESC", $board_name, $board_name, $board_name, $board_name, $board_name, $board_name, $board_name)) or error(db_error());
			
			while ($post = $query->fetch(PDO::FETCH_ASSOC)) {
				$post['link'] = $config['root'] . $board['dir'] . $config['dir']['res'] . sprintf($config['file_page'], ($post['thread'] ? $post['thread'] : $post['id']));
				$post['board_name'] = $board['name'];
				if (isset($post['files']))
					$files = json_decode($post['files']);
                    if ($files[0]->file == 'deleted') continue;
					$post['file'] = $config['uri_thumb'] . $files[0]->thumb;

                                if ($settings['use_tooltipster']) {
                                        $post['muhdifference'] = ago(time() - $post['time']);
                       
                                        if ($post['last_reply'])
                                                $post['last_reply_difference'] = ago(time() - $post['last_reply']);
                                }


				$recent_posts[] = $post;
			}
			
			file_write($config['dir']['home'] . $board_name . '/catalog.html', Element('themes/catalog/catalog.html', Array(
				'settings' => $settings,
				'config' => $config,
				'boardlist' => createBoardlist(),
				'recent_images' => $recent_images,
				'recent_posts' => $recent_posts,
				'stats' => $stats,
				'board' => $board_name,
				'link' => $config['root'] . $board['dir']
			)));
		}
	};
