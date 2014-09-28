<?php
$theme = array(
    'name'        => 'Donate',
    'description' => 'le wales face',
    'version'     => 'v9001',

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
              'default' => 'donate.html')),

    'build_function'   => 'donate_build');
?>
