<?php
	if($_SERVER['SCRIPT_FILENAME'] == str_replace('\\', '/', __FILE__)) {
		// You cannot request this file directly.
		header('Location: ../', true, 302);
		exit;
	}
	
	function sql_open() {
		global $pdo, $config;
		if($pdo) return true;
		
		$dsn = $config['db']['type'] . ':host=' . $config['db']['server'] . ';dbname=' . $config['db']['database'];
		if(!empty($config['db']['dsn']))
			$dsn .= ';' . $config['db']['dsn'];
		try {
			return $pdo = new PDO($dsn, $config['db']['user'], $config['db']['password']);
		} catch(PDOException $e) {
			$message = $e->getMessage();
			
			// Remove any sensitive information
			$message = str_replace($config['db']['user'], '<em>hidden</em>', $message);
			$message = str_replace($config['db']['password'], '<em>hidden</em>', $message);
			
			// Print error
			error('Database error: ' . $message);
		}
	}
	
	function sql_close() {
		global $pdo;
		$pdo = NULL;
	}
	
	function prepare($query) {
		global $pdo;
		return $pdo->prepare($query);
	}
	
	function query($query) {
		global $pdo;
		return $pdo->query($query);
	}
	
	function db_error($PDOStatement=null) {
		global $pdo;
		if(isset($PDOStatement)) {
			$err = $PDOStatement->errorInfo();
			return $err[2];
		} else {
			$err = $pdo->errorInfo();
			return $err[2];
		}
	}
?>