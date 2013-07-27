<?php
 require_once 'inc/functions.php';
 checkBan();
 print "<!doctype html><html><head><meta charset='utf-8'><title>"._("Banned?")."</title></head><body>";
 print "<h1>"._("You are not banned.")."</h1>";
 print "</body></html>";
?>
