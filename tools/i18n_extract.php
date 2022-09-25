#!/usr/bin/php
<?php

/*
 *  i18n_extract.php - extracts the strings and updates all locales
 *
 *  Options:
 *    -l [locale], --locale=[locale]
 *      Updates only [locale] locale. If it does not exist yet, we create a new directory.
 *      
 */

require __DIR__ . '/inc/cli.php';

// parse command line
$opts = getopt('l:', ['locale:']);
$options = [];

$options['locale'] = $opts['l'] ?? $opts['locale'] ?? false;

$locales = glob("inc/locale/*");
$locales = array_map("basename", $locales);

if ($options['locale']) $locales = [$options['locale']];


foreach ($locales as $loc) {
	if (file_exists ($locdir = "inc/locale/".$loc)) {
		if (!is_dir ($locdir)) {
			continue;
		}
	}
	else {
		mkdir($locdir);
		mkdir($locdir."/LC_MESSAGES");
	}

	// Generate tinyboard.po
	if (file_exists($locdir."/LC_MESSAGES/tinyboard.po"))	$join = "-j";
	else							$join = "";
	passthru("cd $locdir/LC_MESSAGES;
         xgettext -d tinyboard -L php --from-code utf-8 $join -c $(find ../../../../ -name \*.php)");

	// Generate javascript.po
	passthru("cd $locdir/LC_MESSAGES;".
         "xgettext -d javascript -L Python --force-po --from-code utf-8 $join -c ".
	 "$(find ../../../../js/ ../../../../templates/ -not -path \*node_modules\* -name \*.js)");
}
