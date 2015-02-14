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
              'default' => 'irc.html')),

    'build_function'   => 'irc_build');
?>
