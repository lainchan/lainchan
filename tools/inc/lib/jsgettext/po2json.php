<?php

	require_once 'PoeditParser.php';

	function buildOptions($args) {
		$options = ['-o'	=> null, '-i'	=> null, '-n'	=> 'l10n'];
		$len = is_countable($args) ? count($args) : 0;
		$i = 0;
		while ($i < $len) {
			if (preg_match('#^-[a-z]$#i', (string) $args[$i])) {
				$options[$args[$i]] = isset($args[$i+1]) ? trim((string) $args[$i+1]) : true;
				$i += 2;
			}
			else {
				$options[] = $args[$i];
				$i++;
			}
		}
		return $options;
	}

	$options = buildOptions($argv);

	if (!file_exists($options['-i']) || !is_readable($options['-i'])) {
		die("Invalid input file. Make sure it exists and is readable.");
	}

	$poeditParser = new PoeditParser($options['-i']);
	$poeditParser->parse();
	
	if ($poeditParser->toJSON($options['-o'], $options['-n'])) {
		$strings = is_countable($poeditParser->getStrings()) ? count($poeditParser->getStrings()) : 0;
		echo "Successfully exported " . (is_countable($strings) ? count($strings) : 0) . " strings.\n";
	}
	else {
		echo "Cannor write to file '{$options['-o']}'.\n";
	}
?>
