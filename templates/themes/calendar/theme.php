<?php
	require 'info.php';
	
	function calendar_build($action, $settings, $board) {
		// Possible values for $action:
		//	- all (rebuild everything, initialization)
		//	- news (news has been updated)
		//	- boards (board list changed)
		
		Calendar::build($action, $settings);
	}
	
	// Wrap functions in a class so they don't interfere with normal Tinyboard operations
	class Calendar {
		public static function build($action, $settings) {
			global $config;
			
			if ($action == 'all' || $action == 'news')
				file_write($config['dir']['home'] . $settings['file'], Calendar::htmlpage($settings));
				file_write($config['dir']['home'] . "calendarpost.php", Calendar::phppage($settings));
		}
		
		// Build staff application page
		public static function htmlpage($settings) {
			global $config;
			
			return Element('themes/calendar/calendar.html', Array(
				'settings' => $settings,
				'config' => $config,
				'boardlist' => createBoardlist()
			));
		}
		public static function phppage($settings) {
			global $config;
			$page = file_get_contents('templates/themes/calendar/calendarpost.php');
			return $page;
		}
	};
	
?>
