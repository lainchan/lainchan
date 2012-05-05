<?php
	$theme = Array();
	
	// Theme name
	$theme['name'] = 'RRDtool';
	// Description (you can use Tinyboard markup here)
	$theme['description'] = 'Graph basic statistics using the PHP RRDtool extension.';
	$theme['version'] = 'v0.2';
	
	// Theme configuration	
	$theme['config'] = Array();
	
	$theme['config'][] = Array(
		'title' => 'Path',
		'name' => 'path',
		'type' => 'text',
		'default' => str_replace('\\', '/', dirname(__FILE__)) . '/data',
		'size' => '50'
	);
	
	$theme['config'][] = Array(
		'title' => 'Images path',
		'name' => 'images',
		'type' => 'text',
		'default' => str_replace('\\', '/', dirname(__FILE__)) . '/images',
		'size' => '50'
	);
	
	$__boards = listBoards();
	$__default_boards = Array();
	foreach ($__boards as $__board)
		$__default_boards[] = $__board['uri'];
	
	$theme['config'][] = Array(
		'title' => 'Boards',
		'name' => 'boards',
		'type' => 'text',
		'comment' => '(boards to graph; space seperated)',
		'size' => 24,
		'default' => implode(' ', $__default_boards)
	);
	
	$theme['config'][] = Array(
		'title' => 'Excluded Boards',
		'name' => 'boards_exclude',
		'type' => 'text',
		'comment' => '(above boards to exclude from the "combined" graph)',
		'size' => 24
	);
	
	$theme['config'][] = Array(
		'title' => 'Interval',
		'name' => 'interval',
		'type' => 'text',
		'comment' => '(minutes between updates; max: 86400)',
		'size' => 3,
		'default' => '2'
	);
	
	$theme['config'][] = Array(
		'title' => 'Graph Width',
		'name' => 'width',
		'type' => 'text',
		'size' => 3,
		'default' => '700'
	);
	
	$theme['config'][] = Array(
		'title' => 'Graph Height',
		'name' => 'height',
		'type' => 'text',
		'size' => 3,
		'default' => '150'
	);
	
	$theme['config'][] = Array(
		'title' => 'Graph Rate',
		'name' => 'rate',
		'type' => 'text',
		'comment' => 'Graph posts per X? ("minute", "day", "year", etc.)',
		'size' => 3,
		'default' => 'hour'
	);
	
	$theme['install_callback'] = 'rrdtool_install';
	if (!function_exists('rrdtool_install')) {
		function rrdtool_install($settings) {
			global $config;
			
			if (!is_numeric($settings['interval']) || $settings['interval'] < 1 || $settings['interval'] > 86400)
				return Array(false, 'Invalid interval: <strong>' . $settings['interval'] . '</strong>. Must be an integer greater than 1 and less than 86400.');
			
			if (!is_numeric($settings['width']) || $settings['width'] < 1)
				return Array(false, 'Invalid width: <strong>' . $settings['width'] . '</strong>!');
			
			if (!is_numeric($settings['height']) || $settings['height'] < 1)
				return Array(false, 'Invalid height: <strong>' . $settings['height'] . '</strong>!');
			
			if (!in_array($settings['rate'], Array('second', 'minute', 'day', 'hour', 'week', 'month', 'year')))
				return Array(false, 'Invalid rate: <strong>' . $settings['rate'] . '</strong>!');
			
			$job = '*/' . $settings['interval'] . ' * * * * php -q ' . str_replace('\\', '/', dirname(__FILE__)) . '/cron.php' . PHP_EOL;
			
			if (function_exists('system')) {
				$crontab = tempnam($config['tmp'], 'tinyboard-rrdtool');
				file_write($crontab, $job);
				@system('crontab ' . escapeshellarg($crontab), $ret);
				unlink($crontab);
				
				if ($ret === 0)
					return ''; // it seems to install okay?
			}
			
			return Array(true, '<h2>I couldn\'t install the crontab!</h2>' . 
				'In order to use this plugin, you must add the following crontab entry (`crontab -e`):' . 
				'<pre>' . $job . '</pre>');
		}
	}
	
	// Unique function name for building everything
	$theme['build_function'] = 'rrdtool_build';
?>
