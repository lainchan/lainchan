<?php

	// Generates a <ul> element with a list of linked
	// boards and their subtitles.
	function ulBoards() {
		$body = '<ul>';
		
		// List of boards
		$boards = listBoards();
		
		foreach($boards as &$b) {
			$body .= '<li>' . 
				'<a href="?/' .
						sprintf(BOARD_PATH, $b['uri']) . FILE_INDEX .
						'">' .
					sprintf(BOARD_ABBREVIATION, $b['uri']) .
					'</a>' .
					(isset($b['subtitle']) ? ' - ' . $b['subtitle'] : '') . 
				'</li>';
		}
		
		return $body . '</ul>';
	}

?>