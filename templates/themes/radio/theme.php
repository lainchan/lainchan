<?php
require 'info.php';
	
function radio_build($action, $settings, $board) {
  radio::build($action, $settings);
}

class radio {
  public static function build($action, $settings) {
    global $config;

    if ($action == 'all') {
      file_write($config['dir']['home'] . $settings['file'], radio::install($settings));
    }
  }

  public static function install($settings) {
    global $config;

    return Element('themes/radio/radio.html',
                   ['settings'  => $settings, 'config'    => $config, 'boardlist' => createBoardlist()]);
  }
}

?>
