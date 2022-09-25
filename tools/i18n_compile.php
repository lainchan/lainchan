#!/usr/bin/php
<?php

/*
 *  i18n_compile.php - compiles the i18n
 *
 *  Options:
 *    -l [locale], --locale=[locale]
 *      Compiles [locale] locale.
 *      
 */

require __DIR__ . '/inc/cli.php';

// parse command line
$opts = getopt('l:', ['locale:']);
$options = [];

$options['locale'] = $opts['l'] ?? $opts['locale'] ?? false;

if ($options['locale'])	$locales = [$options['locale']];
else			die("Error: no locales specified; use -l switch, eg. -l pl_PL\n");

foreach ($locales as $loc) {
	if (file_exists ($locdir = "inc/locale/".$loc)) {
		if (!is_dir ($locdir)) {
			continue;
		}
	}
	else {
		die("Error: $locdir does not exist\n");
	}

	// Generate tinyboard.po
	if (file_exists($locdir."/LC_MESSAGES/tinyboard.po"))	$join = "-j";
	else							$join = "";
	passthru("cd $locdir/LC_MESSAGES;
         msgfmt tinyboard.po -o tinyboard.mo");

	// Generate javascript.po
	passthru("cd tools/inc/lib/jsgettext/;
         php po2json.php -i ../../../../$locdir/LC_MESSAGES/javascript.po \
                         -o ../../../../$locdir/LC_MESSAGES/javascript.js");
}
