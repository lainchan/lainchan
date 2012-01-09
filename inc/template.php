<?php
	if($_SERVER['SCRIPT_FILENAME'] == str_replace('\\', '/', __FILE__)) {
		// You cannot request this file directly.
		header('Location: ../', true, 302);
		exit;
	}
	
	require 'contrib/Twig/Autoloader.php';
	Twig_Autoloader::register();
	
	Twig_Autoloader::autoload('Twig_Extensions_Node_Trans');
	Twig_Autoloader::autoload('Twig_Extensions_TokenParser_Trans');
	Twig_Autoloader::autoload('Twig_Extensions_Extension_I18n');
	Twig_Autoloader::autoload('Twig_Extensions_Extension_Tinyboard');
	
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
			$options['body'] .= '<h3>Debug</h3><pre style="white-space: pre-wrap;font-size: 10px;">' . str_replace("\n", '<br/>', utf8tohtml(print_r($debug, true))) . '</pre>';
		}
		
		$loader->setPaths($config['dir']['template']);
		
		$twig = new Twig_Environment($loader, Array(
			'autoescape' => false,
			'cache' => "{$config['dir']['template']}/cache",
			'debug' => ($config['debug'] ? true : false),
		));
		$twig->addExtension(new Twig_Extensions_Extension_Tinyboard());
		$twig->addExtension(new Twig_Extensions_Extension_I18n());
		
		// Read the template file
		if(@file_get_contents("{$config['dir']['template']}/${templateFile}")) {
			$body = $twig->render($templateFile, $options);
			
			if($config['minify_html'] && preg_match('/\.html$/', $templateFile)) {
				$body = trim(preg_replace("/[\t\r\n]/", '', $body));
			}
			
			return $body;
		} else {
			throw new Exception("Template file '${templateFile}' does not exist or is empty in '{$config['dir']['template']}'!");
		}
	}
?>
