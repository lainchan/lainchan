<?php

require 'inc/lib/IP/Lifo/IP/IP.php';
require 'inc/lib/IP/Lifo/IP/BC.php';
require 'inc/lib/IP/Lifo/IP/CIDR.php';

use Lifo\IP\CIDR;

class Bans {
	static public function range_to_string($mask) {
		list($ipstart, $ipend) = $mask;
		
		if (!isset($ipend) || $ipend === false) {
			// Not a range. Single IP address.
			$ipstr = inet_ntop($ipstart);
			return $ipstr;
		}
		
		if (strlen($ipstart) != strlen($ipend))
			return '???'; // What the fuck are you doing, son?
		
		$range = CIDR::range_to_cidr(inet_ntop($ipstart), inet_ntop($ipend));
		if ($range !== false)
			return $range;
		
		return '???';
	}
	
	private static function calc_cidr($mask) {
		$cidr = new CIDR($mask);
		$range = $cidr->getRange();
		
		return array(inet_pton($range[0]), inet_pton($range[1]));
	}
	
	public static function parse_time($str) {
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
	
	static public function parse_range($mask) {
		$ipstart = false;
		$ipend = false;
		
		if (preg_match('@^(\d{1,3}\.){1,3}([\d*]{1,3})?$@', $mask) && substr_count($mask, '*') == 1) {
			// IPv4 wildcard mask
			$parts = explode('.', $mask);
			$ipv4 = '';
			foreach ($parts as $part) {
				if ($part == '*') {
					$ipstart = inet_pton($ipv4 . '0' . str_repeat('.0', 3 - substr_count($ipv4, '.')));
					$ipend = inet_pton($ipv4 . '255' . str_repeat('.255', 3 - substr_count($ipv4, '.')));
					break;
				} elseif(($wc = strpos($part, '*')) !== false) {
					$ipstart = inet_pton($ipv4 . substr($part, 0, $wc) . '0' . str_repeat('.0', 3 - substr_count($ipv4, '.')));
					$ipend = inet_pton($ipv4 . substr($part, 0, $wc) . '9' . str_repeat('.255', 3 - substr_count($ipv4, '.')));
					break;
				}
				$ipv4 .= "$part.";
			}
		} elseif (preg_match('@^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/\d+$@', $mask)) {
			list($ipv4, $bits) = explode('/', $mask);
			if ($bits > 32)
				return false;
			
			list($ipstart, $ipend) = self::calc_cidr($mask);
		} elseif (preg_match('@^[:a-z\d]+/\d+$@i', $mask)) {
			list($ipv6, $bits) = explode('/', $mask);
			if ($bits > 128)
				return false;
			
			list($ipstart, $ipend) = self::calc_cidr($mask);
		} else {
			if (($ipstart = @inet_pton($mask)) === false)
				return false;
		}
		
		return array($ipstart, $ipend);
	}
	
	static public function find($ip, $board = false, $get_mod_info = false) {
		global $config;
		
		$query = prepare('SELECT ``bans``.*' . ($get_mod_info ? ', `username`' : '') . ' FROM ``bans``
		' . ($get_mod_info ? 'LEFT JOIN ``mods`` ON ``mods``.`id` = `creator`' : '') . '
		WHERE
			(' . ($board ? '(`board` IS NULL OR `board` = :board) AND' : '') . '
			(`ipstart` = :ip OR (:ip >= `ipstart` AND :ip <= `ipend`)))
		ORDER BY `expires` IS NULL, `expires` DESC');
		
		if ($board)
			$query->bindValue(':board', $board);
		
		$query->bindValue(':ip', inet_pton($ip));
		$query->execute() or error(db_error($query));
		
		$ban_list = array();
		
		while ($ban = $query->fetch(PDO::FETCH_ASSOC)) {
			if ($ban['expires'] && ($ban['seen'] || !$config['require_ban_view']) && $ban['expires'] < time()) {
				self::delete($ban['id']);
			} else {
				if ($ban['post'])
					$ban['post'] = json_decode($ban['post'], true);
				$ban['mask'] = self::range_to_string(array($ban['ipstart'], $ban['ipend']));
				$ban_list[] = $ban;
			}
		}
		
		return $ban_list;
	}
	
	static public function list_all($offset = 0, $limit = 9001) {
		$offset = (int)$offset;
		$limit = (int)$limit;
		
		$query = query("SELECT ``bans``.*, `username` FROM ``bans``
			LEFT JOIN ``mods`` ON ``mods``.`id` = `creator`
			ORDER BY `created` DESC LIMIT $offset, $limit") or error(db_error());
		$bans = $query->fetchAll(PDO::FETCH_ASSOC);
		
		foreach ($bans as &$ban) {
			$ban['mask'] = self::range_to_string(array($ban['ipstart'], $ban['ipend']));
		}
		
		return $bans;
	}
	
	static public function count() {
		$query = query("SELECT COUNT(*) FROM ``bans``") or error(db_error());
		return (int)$query->fetchColumn();
	}
	
	static public function seen($ban_id) {
		$query = query("UPDATE ``bans`` SET `seen` = 1 WHERE `id` = " . (int)$ban_id) or error(db_error());
	}
	
	static public function purge() {
		$query = query("DELETE FROM ``bans`` WHERE `expires` IS NOT NULL AND `expires` < " . time() . " AND `seen` = 1") or error(db_error());
	}
	
	static public function delete($ban_id, $modlog = false) {
		if ($modlog) {
			$query = query("SELECT `ipstart`, `ipend` FROM ``bans`` WHERE `id` = " . (int)$ban_id) or error(db_error());
			if (!$ban = $query->fetch(PDO::FETCH_ASSOC)) {
				// Ban doesn't exist
				return false;
			}
			
			$mask = self::range_to_string(array($ban['ipstart'], $ban['ipend']));
			
			modLog("Removed ban #{$ban_id} for " .
				(filter_var($mask, FILTER_VALIDATE_IP) !== false ? "<a href=\"?/IP/$mask\">$mask</a>" : $mask));
		}
		
		query("DELETE FROM ``bans`` WHERE `id` = " . (int)$ban_id) or error(db_error());
		
		return true;
	}
	
	static public function new_ban($mask, $reason, $length = false, $ban_board = false, $mod_id = false, $post = false) {
		global $mod, $pdo, $board;
		
		if ($mod_id === false) {
			$mod_id = isset($mod['id']) ? $mod['id'] : -1;
		}
				
		$range = self::parse_range($mask);
		$mask = self::range_to_string($range);
		
		$query = prepare("INSERT INTO ``bans`` VALUES (NULL, :ipstart, :ipend, :time, :expires, :board, :mod, :reason, 0, :post)");
		
		$query->bindValue(':ipstart', $range[0]);
		if ($range[1] !== false && $range[1] != $range[0])
			$query->bindValue(':ipend', $range[1]);
		else
			$query->bindValue(':ipend', null, PDO::PARAM_NULL);
		
		$query->bindValue(':mod', $mod_id);
		$query->bindValue(':time', time());
		
		if ($reason !== '') {
			$reason = escape_markup_modifiers($reason);
			markup($reason);
			$query->bindValue(':reason', $reason);
		} else
			$query->bindValue(':reason', null, PDO::PARAM_NULL);
		
		if ($length) {
			if (is_int($length) || ctype_digit($length)) {
				$length = time() + $length;
			} else {
				$length = self::parse_time($length);
			}
			$query->bindValue(':expires', $length);
		} else {
			$query->bindValue(':expires', null, PDO::PARAM_NULL);
		}
		
		if ($ban_board)
			$query->bindValue(':board', $ban_board);
		else
			$query->bindValue(':board', null, PDO::PARAM_NULL);
		
		if ($post) {
			$post['board'] = $board['uri'];
			$query->bindValue(':post', json_encode($post));
		} else
			$query->bindValue(':post', null, PDO::PARAM_NULL);
		
		$query->execute() or error(db_error($query));
		
		if (isset($mod['id']) && $mod['id'] == $mod_id) {
			modLog('Created a new ' .
				($length > 0 ? preg_replace('/^(\d+) (\w+?)s?$/', '$1-$2', until($length)) : 'permanent') .
				' ban on ' .
				($ban_board ? '/' . $ban_board . '/' : 'all boards') .
				' for ' .
				(filter_var($mask, FILTER_VALIDATE_IP) !== false ? "<a href=\"?/IP/$mask\">$mask</a>" : $mask) .
				' (<small>#' . $pdo->lastInsertId() . '</small>)' .
				' with ' . ($reason ? 'reason: ' . utf8tohtml($reason) . '' : 'no reason'));
		}
		return $pdo->lastInsertId();
	}
}
