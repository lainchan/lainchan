<?php
	
	function sql_open() {
		global $pdo;
		if($pdo) return true;
		
		try {
			return $pdo = new PDO(DB_TYPE . ':host=' . DB_SERVER . ';dbname=' . DB_DATABASE, DB_USER, DB_PASSWORD);
		} catch(PDOException $e) {
			$message = $e->getMessage();
			
			// Remove any sensitive information
			$message = str_replace(DB_USER, '<em>hidden</em>', $message);
			$message = str_replace(DB_PASSWORD, '<em>hidden</em>', $message);
			
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