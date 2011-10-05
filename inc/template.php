<?php
	if($_SERVER['SCRIPT_FILENAME'] == str_replace('\\', '/', __FILE__)) {
		// You cannot request this file directly.
		header('Location: ../', true, 302);
		exit;
	}
	
	require 'contrib/Twig/Autoloader.php';
	Twig_Autoloader::register();
	
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
			'cache' => 'cache',
			'debug' => ($config['debug'] ? true : false),
		));
		
		// Read the template file
		if(@file_get_contents("{$config['dir']['template']}/${templateFile}")) {
			return $twig->render($templateFile, $options);
		} else {
			throw new Exception("Template file '${templateFile}' does not exist or is empty in '{$config['dir']['template']}'!");
		}
	}
?>
