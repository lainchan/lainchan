<?PHP

function error_handler($errno,$errstr,$errfile, $errline, $errcontext){
	if(error_reporting() & $errno){
		error($errstr . ' in ' . $errfile . ' at line ' . $errline);
	}
	return false;
}

set_error_handler('error_handler');

function exception_handler(Exception $e){
	error($e->getMessage());
}

set_exception_handler('exception_handler');

function fatal_error_handler(){
	if (($error = error_get_last()) && $error['type'] == E_ERROR) {
		error('Caught fatal error: ' . $error['message'] . ' in ' . $error['file'] . ' at line ' . $error['line']);
	}
}

register_shutdown_function('fatal_error_handler');

$error_recursion=false;

function error($message, $priority = true, $debug_stuff = false) {
	global $board, $mod, $config, $db_error, $error_recursion;
	
	if($error_recursion!==false){
		die("Error recursion detected with " . $message . "<br>Original error:".$error_recursion);
	}
	
	$error_recursion=$message;
	
	if (defined('STDIN')) {
		// Running from CLI
		echo('Error: ' . $message . "\n");
		debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		die();
	}
		
	if(!empty($config)){
	
		if ($config['syslog'] && $priority !== false) {
			// Use LOG_NOTICE instead of LOG_ERR or LOG_WARNING because most error message are not significant.
			_syslog($priority !== true ? $priority : LOG_NOTICE, $message);
		}

		if ($config['debug']) {
			$debug_stuff=array();
			if(isset($db_error)){
				$debug_stuff = array_combine(array('SQLSTATE', 'Error code', 'Error message'), $db_error);
			}
			$debug_stuff['backtrace'] = debug_backtrace();
			$pw = $config['db']['password'];
			$debug_callback = function(&$item) use (&$debug_callback, $pw) {
				if (is_array($item)) {
					return array_map($item, $debug_callback);
				}
				return ($item == $pw ? 'hunter2' : $item);
			};
			$debug_stuff = array_map($debug_stuff, $debug_callback);
		}
	}

	if (isset($_POST['json_response'])) {
		header('Content-Type: text/json; charset=utf-8');
		$data=array('error'=>$message);
		if(!empty($config) && $config['debug']){
			$data['debug']=$debug_stuff;
		}
		print json_encode($data);
		exit();
	}

	header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
	
	die(Element('page.html', array(
		'config' => $config,
		'title' => _('Error'),
		'subtitle' => _('An error has occured.'),
		'body' => Element('error.html', array(
			'config' => $config,
			'message' => $message,
			'mod' => $mod,
			'board' => isset($board) ? $board : false,
			'debug' => str_replace("\n", '&#10;', utf8tohtml(print_r($debug_stuff, true)))
		))
	)));
}
