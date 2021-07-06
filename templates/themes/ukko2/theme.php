<?php
	require 'info.php';
	
	function ukko2_build($action, $settings) {
		global $config;

		$ukko2 = new ukko2();
		$ukko2->settings = $settings;

		if (! ($action == 'all' || $action == 'post' || $action == 'post-thread' || $action == 'post-delete')) {
			return;
		}

		$action = generation_strategy('sb_ukko', array());

		if ($action == 'delete') {
			file_unlink($settings['uri'] . '/index.html');
	                 if ($config['api']['enabled']) {
			 	$jsonFilename = $settings['uri'] . '/0.json';
				file_unlink($jsonFilename);
				$jsonFilename = $settings['uri'] . '/catalog.json';
				file_unlink($jsonFilename);
				$jsonFilename = $settings['uri'] . '/threads.json';
				file_unlink($jsonFilename);
			 }
		}
		elseif ($action == 'rebuild') {
			file_write($settings['uri'] . '/index.html', $ukko2->build());
		}
	}
	
	class ukko2 {
		public $settings;
		public function build($mod = false) {
			global $config;
			$boards = listBoards();
			
			$body = '';
			$overflow = array();
			$board = array(
				'dir' => $this->settings['uri'] . "/",
				'url' => $this->settings['uri'],
				'uri' => $this->settings['uri'],
				'name' => $this->settings['title'],
				'title' => sprintf($this->settings['subtitle'], $this->settings['thread_limit'])
			);
			$boardsforukko2 = array();
			$query = '';
			foreach($boards as &$_board) {
				if(in_array($_board['uri'], explode(' ', $this->settings['include']))){
					$query .= sprintf("SELECT *, '%s' AS `board` FROM ``posts_%s`` WHERE `thread` IS NULL UNION ALL ", $_board['uri'], $_board['uri']);
					array_push($boardsforukko2,$_board);
				}
			}
			$query = preg_replace('/UNION ALL $/', 'ORDER BY `sticky` DESC ,`bump` DESC', $query);
			$query = query($query) or error(db_error());

			$count = 0;
			$threads = array();
	                if ($config['api']['enabled']) {
				$apithreads = array(); 
			}	
			while($post = $query->fetch()) {

				if(!isset($threads[$post['board']])) {
					$threads[$post['board']] = 1;
				} else {
					$threads[$post['board']] += 1;
				}
	
				if($count < $this->settings['thread_limit']) {				
					openBoard($post['board']);			
					$thread = new Thread($post, $mod ? '?/' : $config['root'], $mod);

					$posts = prepare(sprintf("SELECT * FROM ``posts_%s`` WHERE `thread` = :id ORDER BY `sticky` DESC, `id` DESC LIMIT :limit", $post['board']));
					$posts->bindValue(':id', $post['id']);
					$posts->bindValue(':limit', ($post['sticky'] ? $config['threads_preview_sticky'] : $config['threads_preview']), PDO::PARAM_INT);
					$posts->execute() or error(db_error($posts));
					
					$num_images = 0;
					while ($po = $posts->fetch()) {
						if ($po['files'])
							$num_images++;
					        $post2 	= new Post($po, $mod ? '?/' : $config['root'], $mod);
						$thread->add($post2);
					
					}
					if ($posts->rowCount() == ($post['sticky'] ? $config['threads_preview_sticky'] : $config['threads_preview'])) {
						$ct = prepare(sprintf("SELECT COUNT(`id`) as `num` FROM ``posts_%s`` WHERE `thread` = :thread UNION ALL SELECT COUNT(`id`) FROM ``posts_%s`` WHERE `files` IS NOT NULL AND `thread` = :thread", $post['board'], $post['board']));
						$ct->bindValue(':thread', $post['id'], PDO::PARAM_INT);
						$ct->execute() or error(db_error($count));
						
						$c = $ct->fetch();
						$thread->omitted = $c['num'] - ($post['sticky'] ? $config['threads_preview_sticky'] : $config['threads_preview']);
						
						$c = $ct->fetch();
						$thread->omitted_images = $c['num'] - $num_images;
					}


					$thread->posts = array_reverse($thread->posts);
					$body .= '<h2><a href="' . $config['root'] . $post['board'] . '">/' . $post['board'] . '/</a></h2>';
					$body .= $thread->build(true);
					if ($config['api']['enabled']) {
						array_push($apithreads,$thread);
					}	
				} else {
					$page = 'index';
					if(floor($threads[$post['board']] / $config['threads_per_page']) > 0) {
						$page = floor($threads[$post['board']] / $config['threads_per_page']) + 1;
					}
					$overflow[] = array('id' => $post['id'], 'board' => $post['board'], 'page' => $page . '.html');
				}

				$count += 1;
			}

			$body .= '<script> var overflow = ' . json_encode($overflow) . '</script>';
			$body .= '<script type="text/javascript" src="/'.$this->settings['uri'].'/ukko.js"></script>';
			
			 // json api
	                 if ($config['api']['enabled']) {
				require_once __DIR__. '/../../../inc/api.php';
				$api = new Api();
				$jsonFilename = $board['dir'] . '0.json';
				$json = json_encode($api->translatePage($apithreads));
	                	file_write($jsonFilename, $json);
				

				$catalog = array();
				$catalog[0] = $apithreads;

				$json = json_encode($api->translateCatalog($catalog));
				$jsonFilename = $board['dir'] . 'catalog.json';
				file_write($jsonFilename, $json);

				$json = json_encode($api->translateCatalog($catalog, true));
				$jsonFilename = $board['dir'] . 'threads.json';
				file_write($jsonFilename, $json);
			}
			$antibot = null;
			if (!$antibot) {
				$antibot = create_antibot($board['uri']);
			}
			$antibot->reset();

			return Element('index.html', array(
				'config' => $config,
				'board' => $board,
				'no_post_form' => $config['overboard_post_form'] ? false : true,
				'body' => $body,
				'mod' => $mod,
				'boardlist' => createBoardlist($mod),
				'boards' => $boardsforukko2,
			        'antibot' => $antibot )
			);
		}
		
	};
	
?>
