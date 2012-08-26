<?php

/*
 *  Copyright (c) 2010-2012 Tinyboard Development Group
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
			
			$debug['sql'][] = Array(
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
		$options = Array(PDO::ATTR_TIMEOUT => $config['db']['timeout']);
		$options = Array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');
		if ($config['db']['persistent'])
			$options[PDO::ATTR_PERSISTENT] = true;
		return $pdo = new PDO($dsn, $config['db']['user'], $config['db']['password'], $options);
	} catch(PDOException $e) {
		$message = $e->getMessage();
		
		// Remove any sensitive information
		$message = str_replace($config['db']['user'], '<em>hidden</em>', $message);
		$message = str_replace($config['db']['password'], '<em>hidden</em>', $message);
		
		// Print error
		error('Database error: ' . $message);
	}
}

function prepare($query) {
	global $pdo, $debug, $config;
	
	sql_open();
	
	if ($config['debug'])
		return new PreparedQueryDebug($query);

	return $pdo->prepare($query);
}

function query($query) {
	global $pdo, $debug, $config;
	
	sql_open();
	
	if ($config['debug']) {
		$start = microtime(true);
		$query = $pdo->query($query);
		if (!$query)
			return false;
		$time = round((microtime(true) - $start) * 1000, 2) . 'ms';
		$debug['sql'][] = Array(
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
