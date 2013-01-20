<?php

/*
 *  Copyright (c) 2010-2013 Tinyboard Development Group
 */

// WARNING: Including this file is DEPRECIATED. It's only here to support older versions and won't exist forever.

if (realpath($_SERVER['SCRIPT_FILENAME']) == str_replace('\\', '/', __FILE__)) {
	// You cannot request this file directly.
	exit;
}

require 'inc/mod/auth.php';

