<?php
	$theme = Array();
	
	// Theme name
	$theme['name'] = 'Frameset';
	// Description (you can use Tinyboard markup here)
	$theme['description'] = 
'Use a basic frameset layout, with a list of boards and pages on a sidebar to the left of the page.

Users never have to leave the homepage; they can do all their browsing from the one page.';
	$theme['version'] = 'v0.1';
	
	// Theme configuration	
	$theme['config'] = Array();
	$theme['config'][] = Array(
		'title' => 'Page title',
		'name' => 'title',
		'type' => 'text'
	);
	
	// Unique function name for building everything
	$config['build_function'] = 'frameset_build';
	
	function frameset_build($settings) {
		Frameset::build($settings);
	}
	
	// Wrap functions in a class so they don't interfere with normal Tinyboard operations
	class Frameset {
		public static function build($settings) {
			global $config;
			
			file_put_contents($config['dir']['home'] . $config['file_index'], Frameset::homepage($settings));
			file_put_contents($config['dir']['home'] . 'sidebar.html', Frameset::sidebar($settings));
		}
		
		// Build homepage
		public static function homepage($settings) {
			global $config;
			
			// HTML5
			return '<!DOCTYPE html><html>'
			. '<head>'
				. '<link rel="stylesheet" media="screen" href="' . $config['url_stylesheet'] . '"/>'
				. '<style type="text/css">'
					. 'iframe{border:none;margin:0;padding:0;height:99%;position:absolute}'
					. 'iframe#sidebar{left:0;top:0;width:15%}'
					. 'iframe#main{border-left:1px solid black;left:15%;top:0;width:85%}'
				. '</style>'
				. '<title>' . $settings['title'] . '</title>'
			. '</head><body>'
			// Sidebar
			. '<iframe src="sidebar.html" id="sidebar"></iframe>'
			// Main
			. '<iframe src="b" id="main"></iframe>'
			// Finish page
			. '</body></html>';
		}
		
		// Build sidebar
		public static function sidebar($settings) {
			global $config, $board;
			
			$body = '<!DOCTYPE html><html>'
			. '<head>' 
				. '<style type="text/css">'
					. ''
				. '</style>'
				. '<base target="main" />'
				. '<title>' . $settings['title'] . '</title>'
			. '</head><body><h2>Boards</h2><ul>';
			
			$boards = listBoards();
			foreach($boards as &$_board) {
				openBoard($_board['uri']);
				$body .= '<li><a href="' .
					sprintf($config['board_path'], $board['uri']) .
				'">' . $board['name'] . '</a></li>';
			}
			
			$body .= '</ul>'
			// Finish page
			. '</body></html>';
			
			return $body;
		}
	};
	
?>