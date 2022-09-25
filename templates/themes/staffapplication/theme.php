<?php
	require 'info.php';
	
	function staffapplication_build($action, $settings, $board) {
		// Possible values for $action:
		//	- all (rebuild everything, initialization)
		//	- news (news has been updated)
		//	- boards (board list changed)
		
		StaffApplication::build($action, $settings);
	}
	
	// Wrap functions in a class so they don't interfere with normal Tinyboard operations
	class StaffApplication {
		public static function build($action, $settings) {
			global $config;
			
			if ($action == 'all' || $action == 'news')
				file_write($config['dir']['home'] . $settings['file'], StaffApplication::htmlpage($settings));
				file_write($config['dir']['home'] . "staffapplication.php", StaffApplication::phppage($settings));
		}
		
		// Build staff application page
		public static function htmlpage($settings) {
			global $config;
			
			return Element('themes/staffapplication/staffapplication.html', ['settings' => $settings, 'config' => $config, 'boardlist' => createBoardlist()]);
		}
		public static function phppage($settings) {
			global $config;
			$page = file_get_contents('templates/themes/staffapplication/staffapplicationpost.php');
			return $page;
		}
	};
	
?>
