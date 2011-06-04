<?php
	class Remote {
		public function __construct($config) {
			foreach($config as $name => $value) {
				$this->{$name} = $value;
			}
			
			$methods = Array();
			
			if(!isset($this->auth['method']))
				error('Unspecified authentication method.');
			
			// Connect
			$this->connection = ssh2_connect($this->host, isset($this->port) ? $this->port : 22, $methods);
			
			switch($this->auth['method']) {
				case 'plain':
					if(!ssh2_auth_password($this->connection, $this->auth['username'], $this->auth['password']))
						error('Plain-text authentication failed.');
					break;
				default:
					error('Unknown authentication method.');
			}
			
		}
		
		public function write($data, $remote_path) {
			global $config;
			
			switch($this->type) {
				case 'sftp':
					$sftp = ssh2_sftp($this->connection);
					file_write('ssh2.sftp://' . $sftp . $remote_path, $data, true);
					break;
				case 'scp':
					$file = tempnam($config['tmp'], 'tinyboard-scp');
					// Write to temp file
					file_write($file, $data);
					
					ssh2_scp_send($this->connection, $file, $remote_path, 0755);
					break;
				default:
					error('Unknown send method.');
			}
		}
	};
?>