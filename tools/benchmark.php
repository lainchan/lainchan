#!/usr/bin/php
<?php
/*
 *  benchmark.php - benchmarks thumbnailing methods
 *
 */

require __DIR__ . '/inc/cli.php';
require 'inc/image.php';

// move back to this directory
chdir(__DIR__);

if((is_countable($argv) ? count($argv) : 0) != 2)
	die("Usage: {$argv[0]} [file]\n");

$file = $argv[1];
$extension = strtolower(substr((string) $file, strrpos((string) $file, '.') + 1));
$out = tempnam($config['tmp'], 'thumb');
$count = 300;

function benchmark($method) {
	global $config, $file, $extension, $out, $count;
	
	$config['thumb_method'] = $method;
	
	printf("Method: %s\nThumbnailing %d times... ", $method, $count);
	
	$start = microtime(true);
	for($i = 0; $i < $count; $i++) {
		$image = new Image($file, $extension);
		$thumb = $image->resize(
			$config['thumb_ext'] ?: $extension,
			$config['thumb_width'],
			$config['thumb_height']
		);
		
		$thumb->to($out);
		$thumb->_destroy();
		$image->destroy();
	}
	$end = microtime(true);
	
	printf("Took %.2f seconds (%.2f/second; %.2f ms)\n", $end - $start, $rate = ($count / ($end - $start)), 1000 / $rate);
	
	unlink($out);
}

benchmark('gd');
if (extension_loaded('imagick')) {
	benchmark('imagick');
} else {
	echo "Imagick extension not loaded... skipping.\n";
}
benchmark('convert');
benchmark('gm');
becnhmark('convert+gifsicle');
