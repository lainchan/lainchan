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
    
        array('title'   => 'Radio Status URL',
              'name'    => 'radiostatus',
              'type'    => 'text',
              'default' => '/radio_assets/status.xsl'),

        array('title'   => 'Radio MP3 Playlist',
              'name'    => 'radiomp3playlist',
              'type'    => 'text',
              'default' => ''),

        array('title'   => 'Radio OGG Playlist',
              'name'    => 'radiooggplaylist',
              'type'    => 'text',
              'default' => ''),
        
	array('title'   => 'Radio MP3 Source',
              'name'    => 'radiomp3source',
              'type'    => 'text',
              'default' => ''),

        array('title'   => 'Radio OGG Source',
              'name'    => 'radiooggsource',
              'type'    => 'text',
              'default' => ''),
	),
    'build_function'   => 'radio_build');
?>
