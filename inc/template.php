<?php
	if($_SERVER['SCRIPT_FILENAME'] == str_replace('\\', '/', __FILE__)) {
		// You cannot request this file directly.
		header('Location: ../', true, 302);
		exit;
	}
	
	require 'contrib/Twig/Autoloader.php';

	Twig_Autoloader::register();
	
	class Tinyboard_Twig_Extension extends Twig_Extension {
		public function getFilters() {
			return Array(
				'filesize' => new Twig_Filter_Function('format_bytes', Array('needs_environment' => false)),
				'truncate' => new Twig_Filter_Function('twig_truncate_filter', array('needs_environment' => false)),
				'truncate_body' => new Twig_Filter_Function('truncate', array('needs_environment' => false)),
				'extension' => new Twig_Filter_Function('twig_extension_filter', array('needs_environment' => false)),
				'sprintf' => new Twig_Filter_Function('sprintf', array('needs_environment' => false)),
				'capcode' => new Twig_Filter_Function('capcode', array('needs_environment' => false)),
				'hasPermission' => new Twig_Filter_Function('twig_hasPermission_filter', array('needs_environment' => false)),
				'date' => new Twig_Filter_Function('twig_date_filter', array('needs_environment' => false)),
				'poster_id' => new Twig_Filter_Function('poster_id', array('needs_environment' => false)),
				'remove_whitespace' => new Twig_Filter_Function('twig_remove_whitespace_filter', array('needs_environment' => false))
			);
		}
		public function getName() {
			return 'tinyboard';
		}
	}
	
	function twig_remove_whitespace_filter($data) {
		return preg_replace('/[\t\r\n]/', '', $data);
	}
	
	function twig_date_filter($date, $format) {
		return date($format, $date);
	}
	
	function twig_hasPermission_filter($mod, $permission, $board) {
		return hasPermission($permission, $board, $mod);
	}
	
	function twig_extension_filter($value, $case_insensitive = true) {
		return 'test';
		$ext = substr($value, strrpos($value, '.') + 1);
		if($case_insensitive)
			$ext = strtolower($ext);		
		return $ext;
	}
	
	function twig_sprintf_filter( $value, $var) {
		return sprintf($value, $var);
	}
	
	function twig_truncate_filter($value, $length = 30, $preserve = false, $separator = '&hellip;') {
		if (strlen($value) > $length) {
			if ($preserve) {
				if (false !== ($breakpoint = strpos($value, ' ', $length))) {
					$length = $breakpoint;
				}
			}
			return substr($value, 0, $length) . $separator;
		}
		return $value;
	}
	
	$loader = new Twig_Loader_Filesystem($config['dir']['template']);
	
	function Element($templateFile, array $options) {
		global $config, $debug, $loader;
		
		if(function_exists('create_pm_header') && ((isset($options['mod']) && $options['mod']) || isset($options['__mod']))) {
			$options['pm'] = create_pm_header();
		}
		
		if(isset($options['body']) && $config['debug']) {
			if(isset($debug['start'])) {
				$debug['time'] = '~' . round((microtime(true) - $debug['start']) * 1000, 2) . 'ms';
				unset($debug['start']);
				
			}
			$options['body'] .= '<h3>Debug</h3><pre style="white-space: pre-wrap;font-size: 10px;">' . print_r($debug, true) . '</pre><hr/>';
		}
		
		$twig = new Twig_Environment($loader, Array(
			'autoescape' => false,
			'cache' => 'templates/cache',
			'debug' => ($config['debug'] ? true : false),
		));
		$twig->addExtension(new Tinyboard_Twig_Extension());
		
		// Read the template file
		if(@file_get_contents("{$config['dir']['template']}/${templateFile}")) {
			return $twig->render($templateFile, $options);
		} else {
			throw new Exception("Template file '${templateFile}' does not exist or is empty in '{$config['dir']['template']}'!");
		}
	}
?>
