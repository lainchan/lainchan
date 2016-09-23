<?php

	require 'info.php';

	/**
 	 * Generate the board's HTML and move it and its JavaScript in place, whence
	 * it's served
	 */
	function semirand_build($action, $settings) {
		global $config;

		if ($action !== 'all' && $action !== 'post' && $action !== 'post-thread' &&
			$action !== 'post-delete')
		{
			return;
		}

		if ($config['smart_build']) {
			file_unlink($settings['uri'] . '/index.html');
		} else {
			$semirand = new semirand($settings);

			// Copy the generated board HTML to its place
			file_write($settings['uri'] . '/index.html', $semirand->build());
			file_write($settings['uri'] . '/semirand.js',
				Element('themes/semirand/semirand.js', array()));
		}
	}

	/**
	 * Encapsulation of the theme's internals
	 */
	class semirand {
		private $settings;

		function __construct($settings) {
			$this->settings = $this->parseSettings($settings);
		}

		/**
		 * Parse and validate configuration parameters passed from the UI
		 */
		private function parseSettings($settings) {
			if (!is_numeric($settings['thread_limit']) ||
				!is_numeric($settings['random_count']) ||
				!is_numeric($settings['recent_count']))
			{
				error('Invalid configuration parameters.', true);
			}

			$settings['exclude']      = explode(' ', $settings['exclude']);
			$settings['thread_limit'] = intval($settings['thread_limit']);
			$settings['random_count'] = intval($settings['random_count']);
			$settings['recent_count'] = intval($settings['recent_count']);

			if ($settings['thread_limit'] < 1 ||
				$settings['random_count'] < 1 ||
				$settings['recent_count'] < 1)
			{
				error('Invalid configuration parameters.', true);
			}

			return $settings;
		}

		/**
 		 * Obtain list of all threads from all non-excluded boards
		 */
		private function fetchThreads() {
			$query   = '';
			$boards  = listBoards(true);

			foreach ($boards as $b) {
				if (in_array($b, $this->settings['exclude']))
					continue;
				// Threads are those posts that have no parent thread
				$query .= "SELECT *, '$b' AS `board` FROM ``posts_$b`` " .
					"WHERE `thread` IS NULL UNION ALL ";
			}

			$query  = preg_replace('/UNION ALL $/', 'ORDER BY `bump` DESC', $query);
			$result = query($query) or error(db_error());

			return $result->fetchAll(PDO::FETCH_ASSOC);
		}

		/**
		 * Retrieve all replies to a given thread
		 */
		private function fetchReplies($board, $thread_id) {
			$query = prepare("SELECT * FROM ``posts_$board`` WHERE `thread` = :id");
			$query->bindValue(':id', $thread_id, PDO::PARAM_INT);
			$query->execute() or error(db_error($query));

			return $query->fetchAll(PDO::FETCH_ASSOC);
		}

		/**
		 * Intersperse random threads between those in bump order
		 */
		private function shuffleThreads($threads) {
			$random_count = $this->settings['random_count'];
			$recent_count = $this->settings['recent_count'];
			$total        = count($threads);

			// Storage for threads that will be randomly interspersed
			$shuffled = array();

			// Ratio of bumped / all threads
			$topRatio = $recent_count / ($recent_count + $random_count);
			// Shuffle the bottom half of all threads
			$random   = array_splice($threads, floor($total * $topRatio));
			shuffle($random);

			// Merge the random and sorted threads into one sequence. The pattern
			// starts at random threads
			while (!empty($threads)) {
				$shuffled = array_merge($shuffled,
					array_splice($random, 0, $random_count),
					array_splice($threads, 0, $recent_count));
			}

			return $shuffled;
		}

		/**
		 * Build the HTML of a single thread in the catalog
		 */
		private function buildOne($post, $mod = false) {
			global $config;

			openBoard($post['board']);
			$thread  = new Thread($post, $mod ? '?/' : $config['root'], $mod);
			$replies = $this->fetchReplies($post['board'], $post['id']);
			// Number of replies to a thread that are displayed beneath it
			$preview_count = $post['sticky'] ? $config['threads_preview_sticky'] :
				$config['threads_preview'];

			// Chomp the last few replies
			$disp_replies   = array_splice($replies, 0, $preview_count);
			$disp_img_count = 0;
			foreach ($disp_replies as $reply) {
				if ($reply['files'] !== '')
					++$disp_img_count;

				// Append the reply to the thread as it's being built
				$thread->add(new Post($reply, $mod ? '?/' : $config['root'], $mod));
			}

			// Count the number of omitted image replies
			$omitted_img_count = count(array_filter($replies, function($p) {
				return $p['files'] !== '';
			}));

			// Set the corresponding omitted numbers on the thread
			if (!empty($replies)) {
				$thread->omitted = count($replies);
				$thread->omitted_images = $omitted_img_count;
			}

			// Board name and link
			$html  = '<h2><a href="' . $config['root'] . $post['board'] . '/">/' .
				$post['board'] . '/</a></h2>';
			// The thread itself
			$html .= $thread->build(true);

			return $html;
		}

		/**
		 * Query the required information and generate the HTML
		 */
		public function build($mod = false) {
			if (!isset($this->settings)) {
				error('Theme is not configured properly.');
			}

			global $config;

			$html     = '';
			$overflow = array();

			// Fetch threads from all boards and chomp the first 'n' posts, depending
			// on the setting
			$threads     = $this->shuffleThreads($this->fetchThreads());
			$total_count = count($threads);
			// Top threads displayed on load
			$top_threads = array_splice($threads, 0, $this->settings['thread_limit']);
			// Number of processed threads by board
			$counts      = array();

			// Output threads up to the specified limit
			foreach ($top_threads as $post) {
				if (array_key_exists($post['board'], $counts)) {
					++$counts[$post['board']];
				} else {
					$counts[$post['board']] = 1;
				}

				$html .= $this->buildOne($post, $mod);
			}

			foreach ($threads as $post) {
				if (array_key_exists($post['board'], $counts)) {
					++$counts[$post['board']];
				} else {
					$counts[$post['board']] = 1;
				}

				$page       = 'index';
				$board_page = floor($counts[$post['board']] / $config['threads_per_page']);
				if ($board_page > 0) {
					$page = $board_page + 1;
				}
				$overflow[] = array(
					'id'    => $post['id'],
					'board' => $post['board'],
					'page'  => $page . '.html'
				);
			}

			$html .= '<script>var ukko_overflow = ' . json_encode($overflow) . '</script>';
			$html .= '<script type="text/javascript" src="/'.$this->settings['uri'].'/semirand.js"></script>';

			return Element('index.html', array(
				'config'       => $config,
				'board'        => array(
					'dir' => $this->settings['uri'] . "/",
					'url'      => $this->settings['uri'],
					'title'    => $this->settings['title'],
					'subtitle' => str_replace('%s', $this->settings['thread_limit'],
						strval(min($this->settings['subtitle'], $total_count))),
				),
				'no_post_form' => true,
				'body'         => $html,
				'mod'          => $mod,
				'boardlist'    => createBoardlist($mod),
			));
		}

	};

	if (!function_exists('array_column')) {
		/**
		 * Pick out values from subarrays by given key
		 */
		function array_column($array, $key) {
			$result = [];
			foreach ($array as $val) {
				$result[] = $val[$key];
			}
			return $result;
		}
	}

