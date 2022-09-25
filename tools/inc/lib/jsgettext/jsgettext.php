<?php

	require_once 'JSParser.php';
	require_once 'PoeditParser.php';

	function buildOptions($args) {
		$options = ['files' => [], '-o'	=> null, '-k'	=> '_'];
		$len = is_countable($args) ? count($args) : 0;
		$i = 1;
		while ($i < $len) {
			if (preg_match('#^-[a-z]$#i', (string) $args[$i])) {
				$options[$args[$i]] = isset($args[$i+1]) ? trim((string) $args[$i+1]) : true;
				$i += 2;
			}
			else {
				$options['files'][] = $args[$i];
				$i++;
			}
		}
		return $options;
	}

	$options = buildOptions($argv);

	if (!file_exists($options['-o'])) {
		touch($options['-o']);
	}

	if (!is_writable($options['-o'])) {
		die("Invalid output file name. Make sure it exists and is writable.");
	}

	$inputFiles = $options['files'];

	if (empty($inputFiles)) {
		die("You did not provide any input file.");
	}

	$poeditParser = new PoeditParser($options['-o']);
	$poeditParser->parse();

	$errors = [];

	foreach ($inputFiles as $f) {
		if (!is_readable($f) || !preg_match('#\.js$#', (string) $f)) {
			$errors[] = ("$f is not a valid javascript file.");
			continue;
		}
		$jsparser = new JSParser($f, explode(' ', (string) $options['-k']));
		$jsStrings = $jsparser->parse();
		$poeditParser->merge($jsStrings);
	}

	if (!empty($errors)) {
		echo "\nThe following errors occured:\n" . implode("\n", $errors) . "\n";
	}

	$poeditParser->save();
?>
