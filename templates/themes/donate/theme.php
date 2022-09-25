<?php

require 'info.php';
	
function donate_build($action, $settings, $board) {
  donate::build($action, $settings);
}
	
class donate {
  public static function build($action, $settings) {
    global $config;

    if ($action == 'all') {
      file_write($config['dir']['home'] . $settings['file'], donate::install($settings));
    }
  }

  public static function install($settings) {
    global $config;
			
    return Element('themes/donate/donate.html',
                   ['settings'  => $settings, 'config'    => $config, 'boardlist' => createBoardlist()]);
  }
}
	
?>
