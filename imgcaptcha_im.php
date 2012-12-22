<?php
 require_once("inc/functions.php"); 
 require_once("inc/imgcaptcha.php");
 $t = $_GET["cr"];
 header("Content-Type: image/png");
 generateImage($t);
?>
