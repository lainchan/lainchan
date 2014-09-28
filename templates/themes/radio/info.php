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
              'default' => 'radio.html')),

    'build_function'   => 'radio_build');
?>