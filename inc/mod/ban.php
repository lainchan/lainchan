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
	
	if (!preg_match('/^((\d+)\s?ye?a?r?s?)?\s?+((\d+)\s?mon?t?h?s?)?\s?+((\d+)\s?we?e?k?s?)?\s?+((\d+)\s?da?y?s?)?((\d+)\s?ho?u?r?s?)?\s?+((\d+)\s?mi?n?u?t?e?s?)?\s?+((\d+)\s?se?c?o?n?d?s?)?$/', $str, $matches))
		return false;
	
	$expire = 0;
	
	if (isset($matches[2])) {
		// Years
		$expire += $matches[2]*60*60*24*365;
	}
	if (isset($matches[4])) {
		// Months
		$expire += $matches[4]*60*60*24*30;
	}
	if (isset($matches[6])) {
		// Weeks
		$expire += $matches[6]*60*60*24*7;
	}
	if (isset($matches[8])) {
		// Days
		$expire += $matches[8]*60*60*24;
	}
	if (isset($matches[10])) {
		// Hours
		$expire += $matches[10]*60*60;
	}
	if (isset($matches[12])) {
		// Minutes
		$expire += $matches[12]*60;
	}
	if (isset($matches[14])) {
		// Seconds
		$expire += $matches[14];
	}
	
	return time() + $expire;
}

function ban($mask, $reason, $length, $board) {
	global $mod, $pdo;
	
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
	
	modLog('Created a new ban (<small>#' . $pdo->lastInsertId() . '</small>) for <strong>' . utf8tohtml($mask) . '</strong> with ' . ($reason ? 'reason: <small>' . $reason . '</small>' : 'no reason'));
}

function unban($id) {
	// TODO: permissions
	
	$query = prepare("DELETE FROM `bans` WHERE `id` = :id");
	$query->bindValue(':id', $id);
	$query->execute() or error(db_error($query));
	
	modLog("Removed ban #{$id}");
}

