<?php
  require_once 'inc/functions.php';
  require_once 'inc/bans.php';
  checkBan();

  //If the user is not banned, show the "not banned" page.
  die(
    Element('page.html', array(
      'title' => _('Not banned!'),
      'config' => $config,
      'nojavascript' => true,
      'body' => Element('notbanned.html', array()
    ))
  )); 
?>
