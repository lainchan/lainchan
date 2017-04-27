<?php
$theme = array(
    'name'        => 'Radio',
    'description' => 'Display a link to the lainchan radio',
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
              'default' => 'radio.html'),
    
        array('title'   => 'HTTP Prefix',
              'name'    => 'httpprefix',
              'type'    => 'text',
              'default' => ''),

        array('title'   => 'Radio Status URL',
              'name'    => 'radiostatus',
              'type'    => 'text',
              'default' => '/radio_assets/status.xsl'),

        array('title'   => 'Radio Prefix',
              'name'    => 'radioprefix',
              'type'    => 'text',
              'default' => ''),

        array('title'   => 'Filelist Prefix',
              'name'    => 'filelistprefix',
              'type'    => 'text',
              'default' => ''),

	array('title'   => 'Channels',
              'name'    => 'channels',
              'type'    => 'text',
              'default' => 'everything cyberia swing'),
	
	array('title'   => 'Default Channel',
              'name'    => 'defaultchannel',
              'type'    => 'text',
              'default' => 'everything'),
	
        array('title'   => 'formats',
              'name'    => 'formats',
              'type'    => 'text',
              'default' => 'mp3 ogg'),
	),
    'build_function'   => 'radio_build');
?>
