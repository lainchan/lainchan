<?php
require 'info.php';
	
function irc_build($action, $settings, $board) {
  irc::build($action, $settings);
}

class irc {
  public static function build($action, $settings) {
    global $config;

    if ($action == 'all') {
      file_write($config['dir']['home'] . $settings['file'], irc::install($settings));
    }
  }

  public static function install($settings) {
    global $config;

    return Element('themes/irc/irc.html',
                   ['settings'  => $settings, 'config'    => $config, 'boardlist' => createBoardlist()]);
  }
}

?>
