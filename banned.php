<?php
 require_once 'inc/functions.php';
 require_once 'inc/bans.php';
 checkBan();
 echo "<!doctype html><html><head><meta charset='utf-8'><title>",_("Banned?"),"</title></head><body>";
 echo "<h1>",_("You are not banned."),"</h1>";
 print "</body></html>";
?>
