<?php

/*
 *  Copyright (c) 2010-2013 Tinyboard Development Group
 */

if (realpath($_SERVER['SCRIPT_FILENAME']) == str_replace('\\', '/', __FILE__)) {
	// You cannot request this file directly.
	exit;
}

class PreparedQueryDebug {
	protected $query;
	
	public function __construct($query) {
		global $pdo;
		$query = preg_replace("/[\n\t]+/", ' ', $query);
		
		$this->query = $pdo->prepare($query);
	}
	public function __call($function, $args) {
		global $config, $debug;
		
		if ($config['debug'] && $function == 'execute') {
			$start = microtime(true);
		}
		
		$return = call_user_func_array(array($this->query, $function), $args);
		
		if ($config['debug'] && $function == 'execute') {
			$time = round((microtime(true) - $start) * 1000, 2) . 'ms';
			
			$debug['sql'][] = array(
				'query' => $this->query->queryString,
				'rows' => $this->query->rowCount(),
				'time' => '~' . $time
			);
		}
		
		return $return;
	}
}

function sql_open() {
	global $pdo, $config;
	if ($pdo) return true;
	
	$dsn = $config['db']['type'] . ':host=' . $config['db']['server'] . ';dbname=' . $config['db']['database'];
	if (!empty($config['db']['dsn']))
		$dsn .= ';' . $config['db']['dsn'];
	try {
		$options = array(
			PDO::ATTR_TIMEOUT => $config['db']['timeout'],
			PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
		);
		if ($config['db']['persistent'])
			$options[PDO::ATTR_PERSISTENT] = true;
		$pdo = new PDO($dsn, $config['db']['user'], $config['db']['password'], $options);
		if (mysql_version() >= 50503)
			query('SET NAMES utf8mb4') or error(db_error());
		else
			query('SET NAMES utf8') or error(db_error());
		return $pdo;
	} catch(PDOException $e) {
		$message = $e->getMessage();
		
		// Remove any sensitive information
		$message = str_replace($config['db']['user'], '<em>hidden</em>', $message);
		$message = str_replace($config['db']['password'], '<em>hidden</em>', $message);
		
		// Print error
		error(_('Database error: ') . $message);
	}
}

// 5.6.10 becomes 50610
function mysql_version() {
	global $pdo;
	
	$version = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
	$v = explode('.', $version);
	if (count($v) != 3)
		return false;
	return (int) sprintf("%02d%02d%02d", $v[0], $v[1], $v[2]);
}

function prepare($query) {
	global $pdo, $debug, $config;
	
	$query = preg_replace('/``([0-9a-zA-Z$_\x{0080}-\x{FFFF}]+)``/u', '`' . $config['db']['prefix'] . '$1`', $query);
	
	sql_open();
	
	if ($config['debug'])
		return new PreparedQueryDebug($query);

	return $pdo->prepare($query);
}

function query($query) {
	global $pdo, $debug, $config;
	
	$query = preg_replace('/``([0-9a-zA-Z$_\x{0080}-\x{FFFF}]+)``/u', '`' . $config['db']['prefix'] . '$1`', $query);
	
	sql_open();
	
	if ($config['debug']) {
		$start = microtime(true);
		$query = $pdo->query($query);
		if (!$query)
			return false;
		$time = round((microtime(true) - $start) * 1000, 2) . 'ms';
		$debug['sql'][] = array(
			'query' => $query->queryString,
			'rows' => $query->rowCount(),
			'time' => '~' . $time
		);
		return $query;
	}

	return $pdo->query($query);
}

function db_error($PDOStatement=null) {
	global $pdo;

	if (isset($PDOStatement)) {
		$err = $PDOStatement->errorInfo();
		return $err[2];
	}

	$err = $pdo->errorInfo();
	return $err[2];
}
