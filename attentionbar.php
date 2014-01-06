<?php
 require_once 'inc/functions.php';
 checkBan();
 $text = isset($_POST['text']) ? $_POST['text'] : '';
 if(strlen($text)>0 && !preg_match('/a href/', $text)) {
	file_put_contents("attentionbar.txt",$text);
	 if(strlen($_SERVER['HTTP_REFERER'])>0) { header('Location: ' . $_SERVER['HTTP_REFERER']); }
 	else { header('Location: /'); }
 } else print(file_get_contents("attentionbar.txt"));
 return;
?>
