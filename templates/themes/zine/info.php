<?php
$theme = array(
    'name'        => 'Zine',
    'description' => 'sex on digital paper',
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
              'default' => 'zine/index.html')),

    'build_function'   => 'zine_build');
?>
