<?php

/*
 *  Copyright (c) 2010-2013 Tinyboard Development Group
 */

if (realpath($_SERVER['SCRIPT_FILENAME']) == str_replace('\\', '/', __FILE__)) {
	// You cannot request this file directly.
	exit;
}

$twig = false;

function load_twig() {
	global $twig, $config;
	
	require 'lib/Twig/Autoloader.php';
	Twig_Autoloader::register();

	Twig_Autoloader::autoload('Twig_Extensions_Node_Trans');
	Twig_Autoloader::autoload('Twig_Extensions_TokenParser_Trans');
	Twig_Autoloader::autoload('Twig_Extensions_Extension_I18n');
	Twig_Autoloader::autoload('Twig_Extensions_Extension_Tinyboard');
	
	$loader = new Twig_Loader_Filesystem($config['dir']['template']);
	$loader->setPaths($config['dir']['template']);
	$twig = new Twig_Environment($loader, array(
		'autoescape' => false,
		'cache' => is_writable('templates') && (!is_dir('templates/cache') || is_writable('templates/cache')) ?
			"{$config['dir']['template']}/cache" : false,
		'debug' => $config['debug']
	));
	$twig->addExtension(new Twig_Extensions_Extension_Tinyboard());
	$twig->addExtension(new Twig_Extensions_Extension_I18n());
}

function Element($templateFile, array $options) {
	global $config, $debug, $twig, $build_pages;
	
	if (!$twig)
		load_twig();
	
	if (function_exists('create_pm_header') && ((isset($options['mod']) && $options['mod']) || isset($options['__mod'])) && !preg_match('!^mod/!', $templateFile)) {
		$options['pm'] = create_pm_header();
	}
	
	if (isset($options['body']) && $config['debug']) {
		if (isset($debug['start'])) {
			$debug['time']['total'] = '~' . round((microtime(true) - $debug['start']) * 1000, 2) . 'ms';
			$debug['time']['init'] = '~' . round(($debug['start_debug'] - $debug['start']) * 1000, 2) . 'ms';
			unset($debug['start']);
			unset($debug['start_debug']);
		}
		if ($config['try_smarter'] && isset($build_pages) && !empty($build_pages))
			$debug['build_pages'] = $build_pages;
		$debug['included'] = get_included_files();
		$debug['memory'] = round(memory_get_usage(true) / (1024 * 1024), 2) . ' MiB';
		$debug['time']['db_queries'] = '~' . round($debug['time']['db_queries'] * 1000, 2) . 'ms';
		$debug['time']['exec'] = '~' . round($debug['time']['exec'] * 1000, 2) . 'ms';
		$options['body'] .=
			'<h3>Debug</h3><pre style="white-space: pre-wrap;font-size: 10px;">' .
				str_replace("\n", '<br/>', utf8tohtml(print_r($debug, true))) .
			'</pre>';
	}
	
	// Read the template file
	if (@file_get_contents("{$config['dir']['template']}/${templateFile}")) {
		$body = $twig->render($templateFile, $options);
		
		if ($config['minify_html'] && preg_match('/\.html$/', $templateFile)) {
			$body = trim(preg_replace("/[\t\r\n]/", '', $body));
		}
		
		return $body;
	} else {
		throw new Exception("Template file '${templateFile}' does not exist or is empty in '{$config['dir']['template']}'!");
	}
}

