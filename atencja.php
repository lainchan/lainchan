<?php
 require_once 'inc/functions.php';
 checkBan(); // Wazne!
 // Zwroc stare dane atencji
 print file_get_contents("atencja.txt");
 // inb4 XSS
 if(strlen($_POST["atencja"])>0) file_put_contents("atencja.txt",$_POST["atencja"]);
 if(strlen($_SERVER['HTTP_REFERER'])>0) { header('Location: ' . $_SERVER['HTTP_REFERER']); }
 else { header('Location: /'); }
?>
