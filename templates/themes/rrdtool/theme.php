<?php
	require 'info.php';
	
	function rrdtool_build($action, $settings) {
		// Possible values for $action:
		//	- all (rebuild everything, initialization)
		//	- news (news has been updated)
		//	- boards (board list changed)
		//	- post (a post has been made)
		
		$b = new TB_RRDTool();
		$b->build($action, $settings);
	}
	
	// Wrap functions in a class so they don't interfere with normal Tinyboard operations
	class TB_RRDTool {
		public function build($action, $settings) {
			global $config, $_theme, $argv;
			
			$this->boards = explode(' ', $settings['boards']);
			$this->spans = Array('minute', 'hour', 'day', 'week', 'month', 'year');
			$this->interval = 120;
			$this->height = 150;
			$this->width = 700;
			
			if($action == 'cron') {
				if(!file_exists($settings['path']))
					mkdir($settings['path']);
				if(!file_exists($settings['images']))
					mkdir($settings['images']);
				
				foreach($this->boards as &$board) {
					$file = $settings['path'] . '/' . $board . '.rrd';
					
					if(!file_exists($file)) {
						// Create graph
						if(!rrd_create($file, Array(
							'-s ' . $this->interval,
							'DS:posts:GAUGE:' . ($this->interval*2) . ':0:10000',
							
							'RRA:MIN:0:1:' .	(3600/$this->interval), // hour
							'RRA:MIN:0:1:' .	(86400/$this->interval), // day
							'RRA:MIN:0:60:' .	(604800/$this->interval), // week
							'RRA:MIN:0:60:' .	(2592000/$this->interval), // month
							'RRA:MIN:0:1440:' .	(31536000/$this->interval), // year
							
							'RRA:AVERAGE:0:1:' .	(3600/$this->interval), // hour
							'RRA:AVERAGE:0:1:' .	(86400/$this->interval), // day
							'RRA:AVERAGE:0:60:' .	(604800/$this->interval), // week
							'RRA:AVERAGE:0:60:' .	(2592000/$this->interval), // month
							'RRA:AVERAGE:0:1440:' .	(31536000/$this->interval), // year
							
							'RRA:MAX:0:1:' .	(3600/$this->interval), // hour
							'RRA:MAX:0:1:' .	(86400/$this->interval), // day
							'RRA:MAX:0:60:' .	(604800/$this->interval), // week
							'RRA:MAX:0:60:' .	(2592000/$this->interval), // month
							'RRA:MAX:0:1440:' .	(31536000/$this->interval), // year
							)))
								error('RRDtool failed: ' . htmlentities(rrd_error()));
					}
					
					// debug just the graphing (not updating) with the --debug switch
					if(!isset($argv[1]) || $argv[1] != '--debug') {
						// Update graph
						$query = prepare(sprintf("SELECT COUNT(*) AS `count` FROM `posts_%s` WHERE `time` >= :time", $board));
						$query->bindValue(':time', time() - $this->interval, PDO::PARAM_INT);
						$query->execute() or error(db_error($query));
						$count = $query->fetch();
						$count = $count['count'];
					
						if(!rrd_update($file, Array(
							'-t',
							'posts',
							'N:' . $count)))
								error('RRDtool failed: ' . htmlentities(rrd_error()));
					}
					
					foreach($this->spans as &$span) {
						// Graph graph
						if(!rrd_graph($settings['images'] . '/' . $board . '-' . $span . '.png', Array(
							'-s -1' . $span,
							'-t Posts on ' . sprintf($config['board_abbreviation'], $board) .' this ',
							'--lazy',
							'-l 0',
							'-h', $this->height, '-w', $this->width,
							'-a', 'PNG',
							'-R', 'mono',
							'-W', 'Powered by Tinyboard',
							'-E',
							'-X', '0',
							'-Y',
							'-v posts/minute',
							'DEF:posts=' . $file . ':posts:AVERAGE',
							'LINE2:posts#663300:Posts',
							'GPRINT:posts:MAX:Max\\: %5.2lf',
							'GPRINT:posts:AVERAGE:Average\\: %5.2lf',
							'GPRINT:posts:LAST:Current\\: %5.2lf posts/min',
							'HRULE:0#000000')))
								error('RRDtool failed: ' . htmlentities(rrd_error()));
					}
				}
				
				// combined graph
				foreach($this->spans as &$span) {
					$options = Array(
						'-s -1' . $span,
						'-t Posts',
						'--lazy',
						'-l 0',
						'-h', $this->height, '-w', $this->width,
						'-a', 'PNG',
						'-R', 'mono',
						'-W', 'Powered by Tinyboard',
						'-E',
						'-X', '0',
						'-Y',
						'-v posts/minute');
					
					$red = 0;
					$green = 0;
					$blue = 0;
					$c = 0;
					$cc = 0;
					
					echo PHP_EOL;
					foreach($this->boards as &$board) {
						$color =	str_pad(dechex($red*85), 2, '0', STR_PAD_LEFT) .
									str_pad(dechex($green*85), 2, '0', STR_PAD_LEFT) .
									str_pad(dechex($blue*85), 2, '0', STR_PAD_LEFT);
						echo $color . '  ' . $c . PHP_EOL;
						
						$options[] = 'DEF:posts' . $board . '=' . $settings['path'] . '/' . $board . '.rrd' . ':posts:AVERAGE';
						$options[] = 'LINE2:posts' . $board . '#' . $color . ':' .
							sprintf($config['board_abbreviation'], $board);
						
						// Randomize colors using this horrible undocumented algorithm I threw together while debugging
						if($c == 0)
							$red++;
						elseif($c == 1)
							$green++;
						elseif($c == 2)
							$blue++;
						elseif($c == 3)
							$green--;
						elseif($c == 4)
							$red--;
						
						$cc++;
						if($cc > 2) {
							$c++;
							$cc = 0;
						}
						if($c>4) $c = 0;
						
						if($red>3) $red = 0;
						if($green>3) $green = 0;
						if($blue>3) $blue = 0;
					}
					$options[] = 'HRULE:0#000000';
					
					if(!rrd_graph($settings['images'] . '/combined-' . $span . '.png', $options))
							error('RRDtool failed: ' . htmlentities(rrd_error()));
				}
			}
		}
	};
	
?>