<?php
$theme = array(
    'name'        => 'IRC',
    'description' => 'Display a link to the lainchan irc',
    'version'     => 'v1',

    'config' => array(
        array('title'   => 'Page title',
              'name'    => 'title',
              'type'    => 'text'),

        array('title'   => 'Slogan',
              'name'    => 'subtitle',
              'type'    => 'text',
              'comment' => '(optional)'),

        array('title'   => 'File',
              'name'    => 'file',
              'type'    => 'text',
              'default' => 'irc.html'),

        array('title'   => 'Channel',
              'name'    => 'channel',
              'type'    => 'text',
              'default' => 'general'),
        
        array('title'   => 'Server',
              'name'    => 'server',
              'type'    => 'text',
              'default' => 'irc.lainchan.org'),
	
	array('title'   => 'Port',
              'name'    => 'port',
              'type'    => 'text',
              'default' => '6697')),

    'build_function'   => 'irc_build');
?>
