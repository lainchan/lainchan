<?php

function config_vars() {
	global $config;
	
	$config_file = file('inc/config.php', FILE_IGNORE_NEW_LINES);
	$conf = array();
	
	$var = array(
		'name' => false,
		'comment' => array(),
		'default' => false,
		'default_temp' => false
	);
	$temp_comment = false;
	foreach ($config_file as $line) {
		if ($temp_comment) {
			$var['comment'][] = $temp_comment;
			$temp_comment = false;
		}
		
		if (preg_match('!^\s*// (.*)$!', $line, $matches)) {
			if ($var['default'] !== false) {
				$line = '';
				$temp_comment = $matches[1];
			} else {
				$var['comment'][] = $matches[1];
			}
		} else if ($var['default_temp'] !== false) {
			$var['default_temp'] .= "\n" . $line;
		} elseif (preg_match('!^\s*\$config\[(.+?)\] = (.+?)(;( //.+)?)?$!', $line, $matches)) {
			$var['name'] = explode('][', $matches[1]);
			if (count($var['name']) == 1) {
				$var['name'] = preg_replace('/^\'(.*)\'$/', '$1', end($var['name']));
			} else {
				foreach ($var['name'] as &$i)
					$i = preg_replace('/^\'(.*)\'$/', '$1', $i);
			}
			
			if (isset($matches[3]))
				$var['default'] = $matches[2];
			else
				$var['default_temp'] = $matches[2];
		}
		
		if (trim($line) === '') {
			if ($var['name'] !== false) {
				if ($var['default_temp'])
					$var['default'] = $var['default_temp'];
				
				$temp = eval('return ' . $var['default'] . ';');
				if (!isset($temp))
					$var['type'] = 'unknown';
				else
					$var['type'] = gettype($temp);
				
				unset($var['default_temp']);
				
				if (!is_array($var['name']) || (end($var['name']) != '' && !in_array(reset($var['name']), array('stylesheets')))) {
					$conf[] = $var;
				}
			}
			
			$var = array(
				'name' => false,
				'comment' => array(),
				'default' => false,
				'default_temp' => false
			);
		}
	}
	
	return $conf;
}

