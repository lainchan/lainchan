<?php

/*
 *  Copyright (c) 2010-2013 Tinyboard Development Group
 */

defined('TINYBOARD') or exit;

$hidden_inputs_twig = array();

class AntiBot {
	public $salt, $inputs = array(), $index = 0;
	
	public static function randomString($length, $uppercase = false, $special_chars = false, $unicode_chars = false) {
		$chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
		if ($uppercase)
			$chars .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		if ($special_chars)
			$chars .= ' ~!@#$%^&*()_+,./;\'[]\\{}|:<>?=-` ';
		if ($unicode_chars) {
			$len = strlen($chars) / 10;
			for ($n = 0; $n < $len; $n++)
				$chars .= mb_convert_encoding('&#' . mt_rand(0x2600, 0x26FF) . ';', 'UTF-8', 'HTML-ENTITIES');
		}
		
		$chars = preg_split('//u', $chars, -1, PREG_SPLIT_NO_EMPTY);
		
		$ch = array();
		
		// fill up $ch until we reach $length
		while (count($ch) < $length) {
			$n = $length - count($ch);
			$keys = array_rand($chars, $n > count($chars) ? count($chars) : $n);
			if ($n == 1) {
				$ch[] = $chars[$keys];
				break;
			}
			shuffle($keys);
			foreach ($keys as $key)
				$ch[] = $chars[$key];
		}
		
		$chars = $ch;
		
		return implode('', $chars);
	}
	
	public static function make_confusing($string) {
		$chars = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
		
		foreach ($chars as &$c) {
			if (mt_rand(0, 3) != 0)
				$c = utf8tohtml($c);
			else
				$c = mb_encode_numericentity($c, array(0, 0xffff, 0, 0xffff), 'UTF-8');
		}
		
		return implode('', $chars);
	}
	
	public function __construct(array $salt = array()) {
		global $config;
		
		if (!empty($salt)) {
			// create a salted hash of the "extra salt"
			$this->salt = implode(':', $salt);
		} else { 
			$this->salt = '';
		}
		
		shuffle($config['spam']['hidden_input_names']);
		
		$input_count = mt_rand($config['spam']['hidden_inputs_min'], $config['spam']['hidden_inputs_max']);
		$hidden_input_names_x = 0;
		
		for ($x = 0; $x < $input_count ; $x++) {
			if ($hidden_input_names_x === false || mt_rand(0, 2) == 0) {
				// Use an obscure name
				$name = $this->randomString(mt_rand(10, 40), false, false, $config['spam']['unicode']);
			} else {
				// Use a pre-defined confusing name
				$name = $config['spam']['hidden_input_names'][$hidden_input_names_x++];
				if ($hidden_input_names_x >= count($config['spam']['hidden_input_names']))
					$hidden_input_names_x = false;
			}
			
			if (mt_rand(0, 2) == 0) {
				// Value must be null
				$this->inputs[$name] = '';
			} elseif (mt_rand(0, 4) == 0) {
				// Numeric value
				$this->inputs[$name] = (string)mt_rand(0, 100000);
			} else {
				// Obscure value
				$this->inputs[$name] = $this->randomString(mt_rand(5, 100), true, true, $config['spam']['unicode']);
			}
		}
	}
	
	public static function space() {
		if (mt_rand(0, 3) != 0)
			return ' ';
		return str_repeat(' ', mt_rand(1, 3));
	}
	
	public function html($count = false) {
		global $config;
		
		$elements = array(
			'<input type="hidden" name="%name%" value="%value%">',
			'<input type="hidden" value="%value%" name="%name%">',
			'<input name="%name%" value="%value%" type="hidden">',
			'<input value="%value%" name="%name%" type="hidden">',
			'<input style="display:none" type="text" name="%name%" value="%value%">',
			'<input style="display:none" type="text" value="%value%" name="%name%">',
			'<span style="display:none"><input type="text" name="%name%" value="%value%"></span>',
			'<div style="display:none"><input type="text" name="%name%" value="%value%"></div>',
			'<div style="display:none"><input type="text" name="%name%" value="%value%"></div>',
			'<textarea style="display:none" name="%name%">%value%</textarea>',
			'<textarea name="%name%" style="display:none">%value%</textarea>'
		);
		
		$html = '';
		
		if ($count === false) {
			$count = mt_rand(1, abs(count($this->inputs) / 15) + 1);
		}
		
		if ($count === true) {
			// all elements
			$inputs = array_slice($this->inputs, $this->index);
		} else {
			$inputs = array_slice($this->inputs, $this->index, $count);
		}
		$this->index += count($inputs);
		
		foreach ($inputs as $name => $value) {
			$element = false;
			while (!$element) {
				$element = $elements[array_rand($elements)];
				$element = str_replace(' ', self::space(), $element);
				if (mt_rand(0, 5) == 0)
					$element = str_replace('>', self::space() . '>', $element);
				if (strpos($element, 'textarea') !== false && $value == '') {
					// There have been some issues with mobile web browsers and empty <textarea>'s.
					$element = false;
				}
			}
			
			$element = str_replace('%name%', utf8tohtml($name), $element);
			
			if (mt_rand(0, 2) == 0)
				$value = $this->make_confusing($value);
			else
				$value = utf8tohtml($value);
			
			if (strpos($element, 'textarea') === false)
				$value = str_replace('"', '&quot;', $value);
			
			$element = str_replace('%value%', $value, $element);
			
			$html .= $element;
		}
		
		return $html;
	}
	
	public function reset() {
		$this->index = 0;
	}
	
	public function hash() {
		global $config;
		
		// This is the tricky part: create a hash to validate it after
		// First, sort the keys in alphabetical order (A-Z)
		$inputs = $this->inputs;
		ksort($inputs);
		
		$hash = '';
		// Iterate through each input
		foreach ($inputs as $name => $value) {
			$hash .= $name . '=' . $value;
		}
		// Add a salt to the hash
		$hash .= $config['cookies']['salt'];
		
		// Use SHA1 for the hash
		return sha1($hash . $this->salt);
	}
}

function _create_antibot($board, $thread) {
	global $config, $purged_old_antispam;
	
	$antibot = new AntiBot(array($board, $thread));
	
	if (!isset($purged_old_antispam)) {
		$purged_old_antispam = true;
		query('DELETE FROM ``antispam`` WHERE `expires` < UNIX_TIMESTAMP()') or error(db_error());
	}
	
	if ($thread)
		$query = prepare('UPDATE ``antispam`` SET `expires` = UNIX_TIMESTAMP() + :expires WHERE `board` = :board AND `thread` = :thread AND `expires` IS NULL');
	else
		$query = prepare('UPDATE ``antispam`` SET `expires` = UNIX_TIMESTAMP() + :expires WHERE `board` = :board AND `thread` IS NULL AND `expires` IS NULL');
	
	$query->bindValue(':board', $board);
	if ($thread)
		$query->bindValue(':thread', $thread);
	$query->bindValue(':expires', $config['spam']['hidden_inputs_expire']);
	$query->execute() or error(db_error($query));
	
	$query = prepare('INSERT INTO ``antispam`` VALUES (:board, :thread, :hash, UNIX_TIMESTAMP(), NULL, 0)');
	$query->bindValue(':board', $board);
	$query->bindValue(':thread', $thread);
	$query->bindValue(':hash', $antibot->hash());
	$query->execute() or error(db_error($query));
	
	return $antibot;
}

function checkSpam(array $extra_salt = array()) {
	global $config, $pdo;

	if (!isset($_POST['hash']))
		return true;

	$hash = $_POST['hash'];

	if (!empty($extra_salt)) {
		// create a salted hash of the "extra salt"
		$extra_salt = implode(':', $extra_salt);
	} else { 
		$extra_salt = '';
	}

	// Reconsturct the $inputs array
	$inputs = array();

	foreach ($_POST as $name => $value) {
		if (in_array($name, $config['spam']['valid_inputs']))
			continue;

		$inputs[$name] = $value;
	}

	// Sort the inputs in alphabetical order (A-Z)
	ksort($inputs);

	$_hash = '';

	// Iterate through each input
	foreach ($inputs as $name => $value) {
		$_hash .= $name . '=' . $value;
	}

	// Add a salt to the hash
	$_hash .= $config['cookies']['salt'];

	// Use SHA1 for the hash
	$_hash = sha1($_hash . $extra_salt);

	if ($hash != $_hash)
		return true;

	$query = prepare('SELECT `passed` FROM ``antispam`` WHERE `hash` = :hash');
	$query->bindValue(':hash', $hash);
	$query->execute() or error(db_error($query));
	if ((($passed = $query->fetchColumn(0)) === false) || ($passed > $config['spam']['hidden_inputs_max_pass'])) {
		// there was no database entry for this hash. most likely expired.
		return true;
	}

	return $hash;
}

function incrementSpamHash($hash) {
	$query = prepare('UPDATE ``antispam`` SET `passed` = `passed` + 1 WHERE `hash` = :hash');
	$query->bindValue(':hash', $hash);
	$query->execute() or error(db_error($query));
}
