<?php

/*
 *  Copyright (c) 2010-2012 Tinyboard Development Group
 */

if (realpath($_SERVER['SCRIPT_FILENAME']) == str_replace('\\', '/', __FILE__)) {
	// You cannot request this file directly.
	exit;
}

function parse_time($str) {
	if (empty($str))
		return false;
	
	if (($time = @strtotime($str)) !== false)
		return $time;
	
	if (!preg_match('/^((\d+)\s*ye?a?r?s?)?\s*+((\d+)\s*mon?t?h?s?)?\s*((\d+)\s*we?e?k?s?)?\s*((\d+)\s*da?y?s?)?((\d+)\s*ho?u?r?s?)?\s*((\d+)\s*mi?n?u?t?e?s?)?\s*+((\d+)\s*se?c?o?n?d?s?)?$/', $str, $matches))
		return false;
	$expire = time();
	
	if (isset($m[2])) {
		// Years
		$expire += $m[2]*60*60*24*365;
	}
	if (isset($m[4])) {
		// Months
		$expire += $m[4]*60*60*24*30;
	}
	if (isset($m[6])) {
		// Weeks
		$expire += $m[6]*60*60*24*7;
	}
	if (isset($m[8])) {
		// Days
		$expire += $m[8]*60*60*24;
	}
	if (isset($m[10])) {
		// Hours
		$expire += $m[10]*60*60;
	}
	if (isset($m[12])) {
		// Minutes
		$expire += $m[12]*60;
	}
	if (isset($m[14])) {
		// Seconds
		$expire += $m[14];
	}
	
	return $expire;
}

function ban($mask, $reason, $length, $board) {
	global $mod;
	
	// TODO: permissions
	
	$query = prepare("INSERT INTO `bans` VALUES (NULL, :ip, :mod, :time, :expires, :reason, :board)");
	$query->bindValue(':ip', $mask);
	$query->bindValue(':mod', $mod['id']);
	$query->bindValue(':time', time());
	if ($reason !== '') {
		markup($reason);
		$query->bindValue(':reason', $reason);
	} else
		$query->bindValue(':reason', null, PDO::PARAM_NULL);
	
	if ($length > 0)
		$query->bindValue(':expires', $length);
	else
		$query->bindValue(':expires', null, PDO::PARAM_NULL);
	
	if ($board)
		$query->bindValue(':board', $board);
	else
		$query->bindValue(':board', null, PDO::PARAM_NULL);
	
	$query->execute() or error(db_error($query));
}

function unban($id) {
	// TODO: permissions
	
	$query = prepare("DELETE FROM `bans` WHERE `id` = :id");
	$query->bindValue(':id', $id);
	$query->execute() or error(db_error($query));
}

