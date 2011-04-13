<?php
	if($_SERVER['SCRIPT_FILENAME'] == str_replace('\\', '/', __FILE__)) {
		// You cannot request this file directly.
		header('Location: ../', true, 302);
		exit;
	}
	
	// -----------------------------------------------------
	// Standard configuration
	//
	// Enable global things like %gentime, etc.
	$templateGlobals = true;
	
	// If $templateGlobals is enabled.
	// Do not change the keys, but the values (if you must), or it will not work. (Prefixed with %)
	$templateGlobalsNames = Array(
								'gentime' => 'gentime',
								'template' => 'template'
								);
	
	// Allow {$phpvar}, etc, to be placed in the template file. This will use a (global) variable defined in PHP.
	// Requires eval() to be enabled. Might be a security risk, so ensure your template files aren't writable before
	// enabling this. (Prefixed with $)
	$templateVariables = false;
	
	// End config
	// -----------------------------------------------------
	
	//'/\{(!?[$%]?[\w\[\]]+)(([=\?:])(([^{^}]|\{.+?\})?)?\}/s'
	
	
	
	
	// Don't change this if you don't know what you're doing.
	// EXTREMELY CONFUSING RECURSION!
	$templateRegex = '/\{(!?[$%]?[\w\[\]]+)(([=\?:])((?>[^{^}]|\{[^{^}]+\}|(?R))+?))?\}/s';
	
	function templateParse($template, array $options, $globals = null, $templateFile = null) {
		global $templateGlobals, $templateGlobalsNames, $templateVariables, $templateRegex;
		//For the global variable {%gentime}
		if($globals == null) {
			$globals = Array();
			if(isset($templateFile)) $globals['template'] = $templateFile;
			$globals['gentime'] = microtime(true);
		}
		
		// What we'll end up finishing with
		$templateBody = '';
		
		$previousPosition = 0;
		// Find the matches
		if(preg_match_all($templateRegex, $template, $templateMatch)) {
			//Iterate through matches
			for($matchIndex=0;$matchIndex<count($templateMatch[0]);$matchIndex++) {
				$optionName = $templateMatch[1][$matchIndex];
				$optionValue = $templateMatch[0][$matchIndex];
				$optionDelim = $templateMatch[3][$matchIndex];
				$optionBlock = $templateMatch[4][$matchIndex];
				$option = (isset($options[$optionName])?$options[$optionName]:null);
				
				$position = strpos($template, $templateMatch[0][$matchIndex]);
				// Replace the found string with "xxxx[...]". ("Bug fix"; allows duplicate tags)
				$template = substr_replace($template, str_repeat('x', strlen($templateMatch[0][$matchIndex])), $position, strlen($templateMatch[0][$matchIndex]));
				
				
				if($optionName[0] == '!') {
					$optionReversed = true;
					$optionName = substr($optionName, 1);
				} else $optionReversed = false;
				
				if($optionName[0] == '%') {
					$tmpOptionName = substr($optionName, 1);
					// $templateGlobals
					if($tmpOptionName == $templateGlobalsNames['gentime']) {
						$option = microtime(true)-$globals['gentime'].'s';
					} elseif(isset($globals[$tmpOptionName])) {
						$option = $globals[$tmpOptionName];
					}
					unset($tmpOptionName);
				}
				
				
				if(preg_match('/(.+?)\[/', $optionName, $optionArrayMatches)) {
					$optionArrayKey = $optionArrayMatches[1];
					$arrayOptionsTemp = $options[$optionArrayKey];
					if(is_array($arrayOptionsTemp)) {
						if(preg_match_all('/\[(.+?)\]/', $optionName, $optionArrayMatches)) {
							for($optionArrayIndex=0;$optionArrayIndex<count($optionArrayMatches[0]);$optionArrayIndex++) {
								if(isset($arrayOptionsTemp[$optionArrayMatches[1][$optionArrayIndex]])) {
									$arrayOptionsTemp = $arrayOptionsTemp[$optionArrayMatches[1][$optionArrayIndex]];
									$option = $arrayOptionsTemp;
								} else break;
							}
						}
					}
				}
				
				if($optionDelim==':') {
					if(isset($option) && $option) {
						if(is_array($option)) {
							$optionValue = '';
							for($optionIndex=0;$optionIndex<count($option);$optionIndex++) {
								$tmpOption = $option[$optionIndex];
								$tmpOptions = $options;
								$tmpOptions[$optionName] = $tmpOption;
								
								
								if($optionIndex == count($option)-1)
									$globals['last'] = true;
								else {
									unset($globals['last']);
									if($optionIndex == 0)
										$globals['first'] = true;
									else
										unset($globals['first']);
								}
								$optionValue .= templateParse($optionBlock, $tmpOptions, $globals);
							}
							unset($tmpOption);
							unset($tmpOptions);
							unset($optionIndex);
							unset($globals['first']);
							unset($globals['last']);
						} else {
							$optionValue = templateParse($optionBlock, $options, $globals);
						}
					} else {
						$optionValue = '';
					}
				} elseif($optionDelim=='?') {
					// Conditionals
					if((!$optionReversed && isset($option) && $option) || ($optionReversed && (!isset($option) || !$option))) {
							/*echo print_r(Array(
								$optionReversed?'reversed':'no',
								isset($option)?'exists':'does not exist',
								$option,
								$optionName
							));*/
							$optionValue = templateParse($optionBlock, $options, $globals);
					} else {
						$optionValue = '';
					}
				} elseif(isset($option)) {
					// If the value is specified...
					if(is_array($option)) {
						$optionValue = implode($option);
					} else {
						$optionValue = $option;
					}
				} elseif($optionDelim=='=') {
					// If it has a default
					$optionValue = templateParse($optionBlock, $options, $globals);
					$options[$optionName] = $optionValue;
				} elseif($templateVariables && $optionName[0] == '$') {
					// Conditionals
					$optionValue = eval("global ${optionName}; return ${optionName};");
				}
				// Append it to the body
				$templateBody .= substr($template, $previousPosition, $position-$previousPosition).$optionValue;
				$previousPosition = $position+strlen($templateMatch[0][$matchIndex]);
				unset($position);
				unset($optionValue);
			}
		}
		// Append the rest of the template
		$templateBody .= substr($template, $previousPosition);
		return $templateBody;
	}
	
	function Element($templateFile, array $options) {
		global $config;
		
		// Small little hack to add the PM system
		if(function_exists('create_pm_header') && (@$options['mod'] || @$options['__mod'])) {
			$options['pm'] = create_pm_header();
		}
		
		// Read the template file
		if($template = @file_get_contents("{$config['dir']['template']}/${templateFile}")) {
			return templateParse($template, $options, null, $templateFile);
		} else {
			throw new Exception("Template file '${templateFile}' does not exist or is empty in '{$config['dir']['template']}'!");
		}
	}
	
?>