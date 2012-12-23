<?php
 require_once 'inc/functions.php';
 checkBan(); // Wazne!
 if(@strlen($_POST["tekst"])>0) {
	file_put_contents("atencja.txt",$_POST["tekst"]);
	 if(strlen($_SERVER['HTTP_REFERER'])>0) { header('Location: ' . $_SERVER['HTTP_REFERER']); }
 	else { header('Location: /'); }
 } else print(file_get_contents("atencja.txt"));
 return;
?>
