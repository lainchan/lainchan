<?php
	require 'info.php';

	function catalog_build($action, $settings, $board) {
		global $config;

		$b = new Catalog($settings);
		$boards = explode(' ', $settings['boards']);

		// Possible values for $action:
		//	- all (rebuild everything, initialization)
		//	- news (news has been updated)
		//	- boards (board list changed)
		//	- post (a reply has been made)
		//	- post-thread (a thread has been made)
		if ($action === 'all') {
			foreach ($boards as $board) {
				$b = new Catalog($settings);

				$action = generation_strategy("sb_catalog", array($board));
				if ($action == 'delete') {
					file_unlink($config['dir']['home'] . $board . '/catalog.html');
					file_unlink($config['dir']['home'] . $board . '/index.rss');
				}
				elseif ($action == 'rebuild') {
					$b->build($settings, $board);
				}
			}
		} elseif ($action == 'post-thread' || ($settings['update_on_posts'] && $action == 'post') || ($settings['update_on_posts'] && $action == 'post-delete')
	       || $action == 'sticky' || ($action == 'lock' && in_array($board, $boards))) {
			$b = new Catalog($settings);

			$action = generation_strategy("sb_catalog", array($board));
			if ($action == 'delete') {
				file_unlink($config['dir']['home'] . $board . '/catalog.html');
				file_unlink($config['dir']['home'] . $board . '/index.rss');
			}
			elseif ($action == 'rebuild') {
				$b->build($settings, $board);
            		}
			$b->build($settings, $board);
        
        }
		// FIXME: Check that Ukko is actually enabled
		if ($settings['enable_ukko'] && (
			$action === 'all' || $action === 'post' ||
			$action === 'post-thread' || $action === 'post-delete' || $action === 'rebuild'))
		{
			$b->buildUkko();
		}
		
		// FIXME: Check that Ukko2 is actually enabled
		if ($settings['enable_ukko2'] && (
			$action === 'all' || $action === 'post' ||
			$action === 'post-thread' || $action === 'post-delete' || $action === 'rebuild'))
		{
			$b->buildUkko2();
		}
		
		// FIXME: Check that Ukko3 is actually enabled
		if ($settings['enable_ukko3'] && (
			$action === 'all' || $action === 'post' ||
			$action === 'post-thread' || $action === 'post-delete' || $action === 'rebuild'))
		{
			$b->buildUkko3();
		}
		// FIXME: Check that Ukko3 is actually enabled
		if ($settings['enable_ukko4'] && (
			$action === 'all' || $action === 'post' ||
			$action === 'post-thread' || $action === 'post-delete' || $action === 'rebuild'))
		{
			$b->buildUkko4();
		}
		// FIXME: Check that Rand is actually enabled
		if ($settings['enable_rand'] && (
			$action === 'all' || $action === 'post' ||
			$action === 'post-thread' || $action === 'post-delete' || $action === 'rebuild'))
		{
			$b->buildRand();
		}
	}

	// Wrap functions in a class so they don't interfere with normal Tinyboard operations
	class Catalog {
		private $settings;
		// Cache for threads from boards that have already been processed
		private $threadsCache = array();

		public function __construct($settings) {
			$this->settings = $settings;
		}

		/**
		 * Build and save the HTML of the catalog for the Ukko theme
		 */
		public function buildUkko() {
			global $config;

			$ukkoSettings = themeSettings('ukko');
 			$queries = array();
			$threads = array();

			$exclusions = explode(' ', $ukkoSettings['exclude']);
			$boards = array_diff(listBoards(true), $exclusions);

			foreach ($boards as $b) {
				if (array_key_exists($b, $this->threadsCache)) {
					$threads = array_merge($threads, $this->threadsCache[$b]);
				} else {
					$queries[] = $this->buildThreadsQuery($b);
				}
			}

			// Fetch threads from boards that haven't beenp processed yet
			if (!empty($queries)) {
				$sql = implode(' UNION ALL ', $queries);
				$res = query($sql) or error(db_error());
				$threads = array_merge($threads, $res->fetchAll(PDO::FETCH_ASSOC));
			}

			// Sort in bump order
			usort($threads, function($a, $b) {
				return strcmp($b['bump'], $a['bump']);
			});
			// Generate data for the template
			$recent_posts = $this->generateRecentPosts($threads);

			$this->saveForBoard($ukkoSettings['uri'], $recent_posts,
				$config['root'] . $ukkoSettings['uri']);
		}
		/**
		 * Build and save the HTML of the catalog for the Ukko2 theme
		 */
		public function buildUkko2() {
			global $config;
			$ukkoSettings = themeSettings('ukko2');
 			$queries = array();
			$threads = array();

			$inclusions = explode(' ', $ukkoSettings['include']);
			$boards = array_intersect(listBoards(true), $inclusions);

			foreach ($boards as $b) {
				if (array_key_exists($b, $this->threadsCache)) {
					$threads = array_merge($threads, $this->threadsCache[$b]);
				} else {
					$queries[] = $this->buildThreadsQuery($b);
				}
			}

			// Fetch threads from boards that haven't beenp processed yet
			if (!empty($queries)) {
				$sql = implode(' UNION ALL ', $queries);
				$res = query($sql) or error(db_error());
				$threads = array_merge($threads, $res->fetchAll(PDO::FETCH_ASSOC));
			}

			// Sort in bump order
			usort($threads, function($a, $b) {
				return strcmp($b['bump'], $a['bump']);
			});
			// Generate data for the template
			$recent_posts = $this->generateRecentPosts($threads);

			$this->saveForBoard($ukkoSettings['uri'], $recent_posts,
				$config['root'] . $ukkoSettings['uri']);
		}
		
		/**
		 * Build and save the HTML of the catalog for the Ukko3 theme
		 */
		public function buildUkko3() {
			global $config;

			$ukkoSettings = themeSettings('ukko3');
 			$queries = array();
			$threads = array();

			$inclusions = explode(' ', $ukkoSettings['include']);
			$boards = array_intersect(listBoards(true), $inclusions);

			foreach ($boards as $b) {
				if (array_key_exists($b, $this->threadsCache)) {
					$threads = array_merge($threads, $this->threadsCache[$b]);
				} else {
					$queries[] = $this->buildThreadsQuery($b);
				}
			}

			// Fetch threads from boards that haven't beenp processed yet
			if (!empty($queries)) {
				$sql = implode(' UNION ALL ', $queries);
				$res = query($sql) or error(db_error());
				$threads = array_merge($threads, $res->fetchAll(PDO::FETCH_ASSOC));
			}

			// Sort in bump order
			usort($threads, function($a, $b) {
				return strcmp($b['bump'], $a['bump']);
			});
			// Generate data for the template
			$recent_posts = $this->generateRecentPosts($threads);

			$this->saveForBoard($ukkoSettings['uri'], $recent_posts,
				$config['root'] . $ukkoSettings['uri']);
		}
		
		/**
		 * Build and save the HTML of the catalog for the Ukko theme
		 */
		public function buildUkko4() {
			global $config;

			$ukkoSettings = themeSettings('ukko4');
 			$queries = array();
			$threads = array();

			$exclusions = explode(' ', $ukkoSettings['exclude']);
			$boards = array_diff(listBoards(true), $exclusions);

			foreach ($boards as $b) {
				if (array_key_exists($b, $this->threadsCache)) {
					$threads = array_merge($threads, $this->threadsCache[$b]);
				} else {
					$queries[] = $this->buildThreadsQuery($b);
				}
			}

			// Fetch threads from boards that haven't beenp processed yet
			if (!empty($queries)) {
				$sql = implode(' UNION ALL ', $queries);
				$res = query($sql) or error(db_error());
				$threads = array_merge($threads, $res->fetchAll(PDO::FETCH_ASSOC));
			}

			// Sort in bump order
			usort($threads, function($a, $b) {
				return strcmp($b['bump'], $a['bump']);
			});
			// Generate data for the template
			$recent_posts = $this->generateRecentPosts($threads);

			$this->saveForBoard($ukkoSettings['uri'], $recent_posts,
				$config['root'] . $ukkoSettings['uri']);
		}
		/**
		 * Build and save the HTML of the catalog for the Rand theme
		 */
		public function buildRand() {
			global $config;

			$randSettings = themeSettings('rand');
 			$queries = array();
			$threads = array();

			$exclusions = explode(' ', $randSettings['exclude']);
			$boards = array_diff(listBoards(true), $exclusions);

			foreach ($boards as $b) {
				if (array_key_exists($b, $this->threadsCache)) {
					$threads = array_merge($threads, $this->threadsCache[$b]);
				} else {
					$queries[] = $this->buildThreadsQuery($b);
				}
			}

			// Fetch threads from boards that haven't beenp processed yet
			if (!empty($queries)) {
				$sql = implode(' UNION ALL ', $queries);
				$res = query($sql) or error(db_error());
				$threads = array_merge($threads, $res->fetchAll(PDO::FETCH_ASSOC));
			}

			// Sort in bump order
			usort($threads, function($a, $b) {
				return strcmp($b['bump'], $a['bump']);
			});
			// Generate data for the template
			$recent_posts = $this->generateRecentPosts($threads);

			$this->saveForBoard($randSettings['uri'], $recent_posts,
				$config['root'] . $randSettings['uri']);
		}

		/**
		 * Build and save the HTML of the catalog for the given board
		 */
		public function build($settings, $board_name) {
			global $config, $board;
			if ($board['uri'] != $board_name) {			
				if (!openBoard($board_name)) {
					error(sprintf(_("Board %s doesn't exist"), $board_name));
				}
			}	

			if (array_key_exists($board_name, $this->threadsCache)) {
				$threads = $this->threadsCache[$board_name];
			} else {
				$sql = $this->buildThreadsQuery($board_name);
				$query = query($sql . ' ORDER BY `bump` DESC') or error(db_error());
				$threads = $query->fetchAll(PDO::FETCH_ASSOC);
				// Save for posterity
				$this->threadsCache[$board_name] = $threads;
			}

			// Generate data for the template
			$recent_posts = $this->generateRecentPosts($threads);

			$this->saveForBoard($board_name, $recent_posts);
		}

		private function buildThreadsQuery($board) {
			$sql  = "SELECT *, `id` AS `thread_id`, " .
				"(SELECT COUNT(`id`) FROM ``posts_$board`` WHERE `thread` = `thread_id`) AS `reply_count`, " .
				"(SELECT SUM(`num_files`) FROM ``posts_$board`` WHERE `thread` = `thread_id` AND `num_files` IS NOT NULL) AS `image_count`, " .
				"'$board' AS `board` FROM ``posts_$board`` WHERE `thread` IS NULL";

			return $sql;
		}

		private function generateRecentPosts($threads) {
			global $config, $board;

			$posts = array();
			foreach ($threads as $post) {
				if ($board['uri'] !== $post['board']) {
					openBoard($post['board']);
				}

				$post['link'] = $config['root'] . $board['dir'] . $config['dir']['res'] . link_for($post);
				$post['board_name'] = $board['name'];

				if ($post['embed'] && preg_match('/^https?:\/\/(\w+\.)?(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9\-_]{10,11})(&.+)?$/i', $post['embed'], $matches)) {
					$post['youtube'] = $matches[2];
				}

				if (isset($post['files']) && $post['files']) {
					$files = json_decode($post['files']);

					if ($files[0]) {
						if ($files[0]->file == 'deleted') {
							if (count($files) > 1) {
								foreach ($files as $file) {
									if (($file == $files[0]) || ($file->file == 'deleted'))
										continue;
									$post['file'] = $config['uri_thumb'] . $file->thumb;
								}

								if (empty($post['file']))
									$post['file'] = $config['image_deleted'];
							} else {
								$post['file'] = $config['image_deleted'];
							}
						} else if($files[0]->thumb == 'spoiler') {
							$post['file'] = '/' . $config['spoiler_image'];
						} else {
							$post['file'] = $config['uri_thumb'] . $files[0]->thumb;
						}
					}
				} else {
					$post['file'] = $config['root'] . $config['image_deleted'];
				}

				if (empty($post['image_count']))
					$post['image_count'] = 0;
				$post['pubdate'] = date('r', $post['time']);

				$posts[] = $post;
			}

			return $posts;
		}

		private function saveForBoard($board_name, $recent_posts, $board_link = null) {
			global $board, $config;

			if ($board_link === null) {
				$board_link = $config['root'] . $board['dir'];
			}

			$required_scripts = array('js/jquery.min.js', 'js/jquery.mixitup.min.js',
				'js/catalog.js');

			// Include scripts that haven't been yet included
			foreach($required_scripts as $i => $s) {
				if (!in_array($s, $config['additional_javascript']))
					$config['additional_javascript'][] = $s;
			}

			file_write($config['dir']['home'] . $board_name . '/catalog.html', Element('themes/catalog/catalog.html', Array(
				'settings' => $this->settings,
				'config' => $config,
				'boardlist' => createBoardlist(),
				'recent_images' => array(),
				'recent_posts' => $recent_posts,
				'stats' => array(),
				'board' => $board_name,
				'link' => $board_link
			)));

			file_write($config['dir']['home'] . $board_name . '/index.rss', Element('themes/catalog/index.rss', Array(
				'settings' => $this->settings,
				'config' => $config,
				'recent_posts' => $recent_posts,
				'board' => $board
			)));
		}
	}
