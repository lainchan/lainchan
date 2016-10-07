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
              'default' => 'donate.html'),

        array('title'   => 'Monthly Amount',
              'name'    => 'monthlyamount',
              'type'    => 'text',
              'default' => '$55'),
          
        array('title'   => 'Current Sticker Amount',
              'name'    => 'stickeramount',
              'type'    => 'text',
              'default' => '$0'),
        
        array('title'   => 'Current Other Donations Amount',
              'name'    => 'otheramount',
              'type'    => 'text',
              'default' => '$0'),
        
        array('title'   => 'Current Progress',
              'name'    => 'currentprogress',
              'type'    => 'text',
              'default' => '$0'),
        
        array('title'   => 'Progress Bar CSS class',
              'name'    => 'progressbarcssclass',
              'type'    => 'text',
              'default' => 'progress-bar red glow'),
        
        array('title'   => 'Progress Bar CSS left',
              'name'    => 'progressbarcssleft',
              'type'    => 'text',
              'default' => '36'),
        
        array('title'   => 'Progress Bar Text CSS left',
              'name'    => 'progressbartextcssleft',
              'type'    => 'text',
              'default' => '135'),
          ),

    'build_function'   => 'donate_build');
?>
