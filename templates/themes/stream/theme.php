<?php
	require 'info.php';
	
	function stream_build($action, $settings, $board) {
		// Possible values for $action:
		//	- all (rebuild everything, initialization)
		//	- boards (board list changed)
		
		Stream::build($action, $settings);
	}
	
	// Wrap functions in a class so they don't interfere with normal Tinyboard operations
	class Stream {
		public static function build($action, $settings) {
			global $config;
			
			if ($action == 'all' )
				file_write($config['dir']['home'] . $settings['file'], Stream::homepage($settings));
		}
		
		// Build news page
		public static function homepage($settings) {
			global $config;
			
			
			return Element('themes/stream/stream.html', ['settings' => $settings, 'config' => $config, 'boardlist' => createBoardlist()]);
		}
	};
	
?>
