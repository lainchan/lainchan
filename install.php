<?php	
	require 'inc/functions.php';
	require 'inc/display.php';
	require 'inc/config.php';
	if (file_exists('inc/instance-config.php')) {
		require 'inc/instance-config.php';
	}
	require 'inc/template.php';
	require 'inc/database.php';
	require 'inc/user.php';
	$step = isset($_GET['step']) ? round($_GET['step']) : 0;
	$page = Array(
		'index' => $config['root'],
		'title' => 'Install',
		'body' => ''
	);
	
	if($step == 0) {
		// Agreeement
		$page['body'] = '
		<textarea style="width:700px;height:370px;margin:auto;display:block;background:white;color:black" disabled>' . htmlentities(file_get_contents('LICENSE')) . '</textarea>
		<p style="text-align:center">
			<a href="?step=1">I have read and understood the agreement. Proceed to installation.</a>
		</p>';
		
		echo Element('page.html', $page);
	} elseif($step == 1) {
		$page['title'] = 'Pre-installation test';
		
		$page['body'] = '<table class="test">';
		
		function rheader($item) {
			global $page, $config;
			
			$page['body'] .= '<tr class="h"><th colspan="2">' . $item . '</th></tr>';
		}
		
		function row($item, $result) {
			global $page, $config;
			
			$page['body'] .= '<tr><th>' . $item . '</th><td><img style="width:16px;height:16px" src="' . $config['dir']['static'] . ($result ? 'ok.png' : 'error.png') . '" /></td></tr>';
		}
		
		
		// Required extensions
		rheader('PHP extensions');
		row('PDO', extension_loaded('pdo'));
		row('GD', extension_loaded('gd'));
		
		// GD tests
		rheader('GD tests');
		row('JPEG', function_exists('imagecreatefromjpeg'));
		row('PNG', function_exists('imagecreatefrompng'));
		row('GIF', function_exists('imagecreatefromgif'));
		row('BMP', function_exists('imagecreatefrombmp'));
		
		// Database drivers
		$drivers = PDO::getAvailableDrivers();
		
		rheader('PDO drivers <em>(currently installed drivers)</em>');
		foreach($drivers as &$driver) {
			row($driver, true);
		}
		
		// Permissions
		rheader('File permissions');
		row('<em>root directory</em> (' . getcwd() . ')', is_writable('.'));
		
		$page['body'] .= '</table>
		<p style="text-align:center">
			<a href="?step=2">Continue.</a>
		</p>';
		
		echo Element('page.html', $page);
	} elseif($step == 2) {
		// Basic config
		$page['title'] = 'Configuration';
		
		function create_salt() {
			return substr(base64_encode(sha1(rand())), 0, rand(25, 31));
		}
		
		$page['body'] = '
	<form action="?step=3" method="post">
		<fieldset>
		<legend>Database</legend>
			<label for="db_type">Type:</label> 
			<select id="db_type" name="db[type]">';
			
			$drivers = PDO::getAvailableDrivers();
			
			foreach($drivers as &$driver) {
				$driver_txt = $driver;
				switch($driver) {
					case 'cubrid':
						$driver_txt = 'Cubrid';
						break;
					case 'dblib':
						$driver_txt = 'FreeTDS / Microsoft SQL Server / Sybase';
						break;
					case 'firebird':
						$driver_txt = 'Firebird/Interbase 6';
						break;
					case 'ibm':
						$driver_txt = 'IBM DB2';
						break;
					case 'informix':
						$driver_txt = 'IBM Informix Dynamic Server';
						break;
					case 'mysql':
						$driver_txt = 'MySQL';
						break;
					case 'oci':
						$driver_txt = 'OCI';
						break;
					case 'odbc':
						$driver_txt = 'ODBC v3 (IBM DB2, unixODBC)';
						break;
					case 'pgsql':
						$driver_txt = 'PostgreSQL';
						break;
					case 'sqlite':
						$driver_txt = 'SQLite 3';
						break;
					case 'sqlite2':
						$driver_txt = 'SQLite 2';
						break;
				}
				$page['body'] .= '<option name="' . $driver . '">' . $driver_txt . '</option>';
			}
			
			$page['body'] .= '	
			</select>
			
			<label for="db_db">Database:</label> 
			<input type="text" id="db_db" name="db[database]" value="" />
			
			<label for="db_user">Username:</label> 
			<input type="text" id="db_user" name="db[user]" value="" />
			
			<label for="db_pass">Password:</label> 
			<input type="password" id="db_pass" name="db[password]" value="" />
		</fieldset>
		
		<fieldset>
		<legend>Cookies</legend>
			<label for="cookies_session">Name of session cookie:</label> 
			<input type="text" id="cookies_session" name="cookies[session]" value="' . session_name() . '" />
			
			<label for="cookies_time">Cookie containing a timestamp of first arrival:</label> 
			<input type="text" id="cookies_time" name="cookies[time]" value="' . $config['cookies']['time'] . '" />
			
			<label for="cookies_hash">Cookie containing a hash for verification purposes:</label> 
			<input type="text" id="cookies_hash" name="cookies[hash]" value="' . $config['cookies']['hash'] . '" />
			
			<label for="cookies_mod">Moderator cookie:</label> 
			<input type="text" id="cookies_mod" name="cookies[mod]" value="' . $config['cookies']['mod'] . '" />
			
			<label for="cookies_salt">Secure salt:</label> 
			<input type="text" id="cookies_salt" name="cookies[salt]" value="' . create_salt() . '" size="40" />
		</fieldset>
		
		<fieldset>
		<legend>Flood control</legend>
			<label for="flood_time">Seconds before each post:</label> 
			<input type="text" id="flood_time" name="flood_time" value="' . $config['flood_time'] . '" />
			
			<label for="flood_time_ip">Seconds before you can repost something (post the exact same text):</label> 
			<input type="text" id="flood_time_ip" name="flood_time_ip" value="' . $config['flood_time_ip'] . '" />
			
			<label for="flood_time_same">Same as above, but with a different IP address:</label> 
			<input type="text" id="flood_time_same" name="flood_time_same" value="' . $config['flood_time_same'] . '" />
			
			<label for="max_body">Maximum post body length:</label> 
			<input type="text" id="max_body" name="max_body" value="' . $config['max_body'] . '" />
			
			<label for="reply_limit">Replies in a thread before it can no longer be bumped:</label> 
			<input type="text" id="reply_limit" name="reply_limit" value="' . $config['reply_limit'] . '" />
			
			<label for="max_links">Maximum number of links in a single post:</label> 
			<input type="text" id="max_links" name="max_links" value="' . $config['max_links'] . '" />			
		</fieldset>
		
		<fieldset>
		<legend>Images</legend>
			<label for="max_filesize">Maximum image filesize:</label> 
			<input type="text" id="max_filesize" name="max_filesize" value="' . $config['max_filesize'] . '" />
			
			<label for="thumb_width">Thumbnail width:</label> 
			<input type="text" id="thumb_width" name="thumb_width" value="' . $config['thumb_width'] . '" />
			
			<label for="thumb_height">Thumbnail height:</label> 
			<input type="text" id="thumb_height" name="thumb_height" value="' . $config['thumb_height'] . '" />
			
			<label for="max_width">Maximum image width:</label> 
			<input type="text" id="max_width" name="max_width" value="' . $config['max_width'] . '" />
			
			<label for="max_height">Maximum image height:</label> 
			<input type="text" id="max_height" name="max_height" value="' . $config['max_height'] . '" />
		</fieldset>
		
		<fieldset>
		<legend>Display</legend>
			<label for="threads_per_page">Threads per page:</label> 
			<input type="text" id="threads_per_page" name="threads_per_page" value="' . $config['threads_per_page'] . '" />
			
			<label for="max_pages">Page limit:</label> 
			<input type="text" id="max_pages" name="max_pages" value="' . $config['max_pages'] . '" />
			
			<label for="threads_preview">Number of replies to show per thread on the index page:</label> 
			<input type="text" id="threads_preview" name="threads_preview" value="' . $config['threads_preview'] . '" />
		</fieldset>
		
		<fieldset>
		<legend>Directories</legend>
			<label for="root">Root URI (include trailing slash):</label> 
			<input type="text" id="root" name="root" value="' . $config['root'] . '" />
			
			<label for="dir_img">Image directory:</label> 
			<input type="text" id="dir_img" name="dir[img]" value="' . $config['dir']['img'] . '" />
			
			<label for="dir_thumb">Thumbnail directory:</label> 
			<input type="text" id="dir_thumb" name="dir[thumb]" value="' . $config['dir']['thumb'] . '" />
			
			<label for="dir_res">Thread directory:</label> 
			<input type="text" id="dir_res" name="dir[res]" value="' . $config['dir']['res'] . '" />
		</fieldset>
		
		<fieldset>
		<legend>Miscellaneous</legend>
			<label for="secure_trip_salt">Secure trip (##) salt:</label> 
			<input type="text" id="secure_trip_salt" name="secure_trip_salt" value="' . create_salt() . '" size="40" />
		</fieldset>
		
		<p style="text-align:center">
			<input type="submit" value="Complete installation" />
		</p>
	</form>
		';
		
		
		echo Element('page.html', $page);
	}
?>