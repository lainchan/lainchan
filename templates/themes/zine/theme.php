<?php

require 'info.php';
	
function zine_build($action, $settings, $board) {
  zine::build($action, $settings);
}
	
class zine {
  public static function build($action, $settings) {
    global $config;

    if ($action == 'all') {
      file_write($config['dir']['home'] . $settings['file'], zine::install($settings));
    }
  }

  public static function install($settings) {
    global $config;
			
    return Element('themes/zine/zine.html',
                   ['settings'  => $settings, 'config'    => $config, 'boardlist' => createBoardlist()]);
  }
}
	
?>
