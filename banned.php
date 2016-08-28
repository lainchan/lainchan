<?php
  require_once 'inc/functions.php';
  require_once 'inc/bans.php';
  checkBan();
  print "<!doctype html><html><head><meta charset='utf-8'><title>"._("Banned?")."</title></head><body>";

  //If the user is not banned, show the "not banned" page.
  die(
    Element('page.html', array(
      'title' => _('Not banned!'),
      'config' => $config,
      'nojavascript' => true,
      'body' => Element('notbanned.html', array()
    ))
  )); 

  print "</body></html>";
?>
