<?php

require_once 'inc/functions.php';
require_once 'inc/lib/securimage/securimage.php';

if(!isset($config['securimage']) || !$config['securimage']){
	error('Securimage captcha not enabled.'); //TODO error image
}

$image=new Securimage(array('config_file'=>__DIR__ . '/inc/captchaconfig.php'));

$image->show();

$code=$image->getCode(false, true);

$ip=$_SERVER['REMOTE_ADDR'];

$query=prepare('INSERT INTO captchas(ip, code, time) VALUES(:ip, :code, NOW())');
$query->bindValue(':ip', $ip);
$query->bindValue(':code', $code);
$query->execute() or error(db_error($query));

$query=prepare('SELECT count(*) from captchas where ip=:ip');
$query->bindValue(':ip', $ip);
$query->execute() or error(db_error($query));

$count=$query->fetch()[0];
if($count>10){
	$query=prepare('DELETE from captchas where ip=:ip ORDER BY time asc LIMIT 1');
	$query->bindValue(':ip', $ip);
	$query->execute()or error(db_error($query));
}
