<?php
	require 'info.php';
	
	function faq_build($action, $settings, $board) {
		// Possible values for $action:
		//	- all (rebuild everything, initialization)
		//	- news (news has been updated)
		//	- boards (board list changed)
		
		FAQ::build($action, $settings);
	}
	
	// Wrap functions in a class so they don't interfere with normal Tinyboard operations
	class FAQ {
		public static function build($action, $settings) {
			global $config;
			
			if ($action == 'all' || $action == 'news')
				file_write($config['dir']['home'] . $settings['file'], FAQ::homepage($settings));
		}
		
		// Build FAQ page
		public static function homepage($settings) {
			global $config;
			return Element('themes/faq/index.html', ['settings' => $settings, 'config' => $config, 'boardlist' => createBoardlist()]);
		}
	};
	
?>
