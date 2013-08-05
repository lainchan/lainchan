<?php
	require 'info.php';
	
	function rrdtool_build($action, $settings, $board) {
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
			
			if (!$settings) {
				error('This theme is not currently installed.');
			}
			
			$this->boards = explode(' ', $settings['boards']);
			$this->spans = Array('hour', 'day', 'week', 'month', 'year');
			// exclude boards from the "combined" graph
			$this->combined_exclude = Array();
			
			if ($action == 'cron') {
				if (!file_exists($settings['path']))
					mkdir($settings['path']);
				if (!file_exists($settings['images']))
					mkdir($settings['images']);
				
				foreach ($this->boards as &$board) {
					$file = $settings['path'] . '/' . $board . '.rrd';
					
					if (!file_exists($file)) {
						// Create graph
						if (!rrd_create($file, Array(
							'-s 60',
							'DS:posts:COUNTER:86400:0:10000',
							
							'RRA:AVERAGE:0:1:60',
							'RRA:AVERAGE:0:1:1440',
							'RRA:AVERAGE:0:30:10080',
							'RRA:AVERAGE:0:120:43829',
							'RRA:AVERAGE:0:1440:525948',
							'RRA:AVERAGE:0:2880:1051897',
							
							'RRA:MAX:0:1:60',
							'RRA:MAX:0:1:1440',
							'RRA:MAX:0:30:10080',
							'RRA:MAX:0:120:43829',
							'RRA:MAX:0:1440:525948',
							'RRA:MAX:0:2880:1051897'
							)))
								error('RRDtool failed: ' . htmlentities(rrd_error()));
					}
					
					// debug just the graphing (not updating) with the --debug switch
					if (!isset($argv[1]) || $argv[1] != '--debug') {
						// Update graph
						$query = query(sprintf("SELECT MAX(`id`) FROM ``posts_%s``", $board));
						$count = $query->fetchColumn();
					
						if (!rrd_update($file, Array(
							'-t',
							'posts',
							'N:' . $count)))
								error('RRDtool failed: ' . htmlentities(rrd_error()));
					}
					
					foreach ($this->spans as &$span) {
						// Graph graph
						if (!rrd_graph($settings['images'] . '/' . $board . '-' . $span . '.png', Array(
							'-s -1' . $span,
							'-t Posts on ' . sprintf($config['board_abbreviation'], $board) .' this ' . $span,
							'--lazy',
							'-l 0',
							'-h', $settings['height'], '-w', $settings['width'],
							'-a', 'PNG',
							'-R', 'mono',
							'-W', 'Powered by Tinyboard',
							'-E',
							'-X', '0',
							'-Y',
							'-v posts/' . $settings['rate'],
							'DEF:posts-second=' . $file . ':posts:AVERAGE',
							'CDEF:posts-minute=posts-second,60,*',
							'CDEF:posts-hour=posts-minute,60,*',
							'CDEF:posts-day=posts-hour,24,*',
							'CDEF:posts-week=posts-day,7,*',
							'CDEF:posts-month=posts-day,28,*',
							'CDEF:posts-year=posts-day,365,*',
							'DEF:posts=' . $file . ':posts:AVERAGE',
							'LINE2:posts-' . $settings['rate'] . '#663300:Posts',
							'GPRINT:posts-' . $settings['rate'] . ':MAX:Max\\: %5.2lf',
							'GPRINT:posts-' . $settings['rate'] . ':AVERAGE:Average\\: %5.2lf',
							'GPRINT:posts-' . $settings['rate'] . ':LAST:Current\\: %5.2lf posts/' . $settings['rate'],
							'HRULE:0#000000')))
								error('RRDtool failed: ' . htmlentities(rrd_error()));
					}
				}
				
				// combined graph
				foreach ($this->spans as &$span) {
					$options = Array(
						'-s -1' . $span,
						'-t Posts this ' . $span,
						'--lazy',
						'-l 0',
						'-h', $settings['height'], '-w', $settings['width'],
						'-a', 'PNG',
						'-R', 'mono',
						'-W', 'Powered by Tinyboard',
						'-E',
						'-X', '0',
						'-Y',
						'-v posts/' . $settings['rate']);
					
					$red = 0;
					$green = 0;
					$blue = 0;
					$c = 0;
					$cc = 0;
					
					$c = 1;
					$cc = 0;
					$red = 2;
					foreach ($this->boards as &$board) {
						if (in_array($board, $this->combined_exclude))
							continue;
						$color =	str_pad(dechex($red*85), 2, '0', STR_PAD_LEFT) .
									str_pad(dechex($green*85), 2, '0', STR_PAD_LEFT) .
									str_pad(dechex($blue*85), 2, '0', STR_PAD_LEFT);
						
						$options[] = 'DEF:posts' . $board . '-second=' . $settings['path'] . '/' . $board . '.rrd' . ':posts:AVERAGE';
						$options[] = 'CDEF:posts' . $board . '-minute=posts' . $board . '-second,60,*';
						$options[] = 'CDEF:posts' . $board . '-hour=posts' . $board . '-minute,60,*';
						$options[] = 'CDEF:posts' . $board . '-day=posts' . $board . '-hour,24,*';
						$options[] = 'CDEF:posts' . $board . '-week=posts' . $board . '-day,7,*';
						$options[] = 'CDEF:posts' . $board . '-month=posts' . $board . '-day,28,*';
						$options[] = 'CDEF:posts' . $board . '-year=posts' . $board . '-day,365,*';
						$options[] = 'LINE2:posts' . $board . '-' . $settings['rate'] . '#' . $color . ':' .
							sprintf($config['board_abbreviation'], $board);
						
						// Randomize colors using this horrible undocumented algorithm I threw together while debugging
						if ($c == 0)
							$red++;
						elseif ($c == 1)
							$green++;
						elseif ($c == 2)
							$blue++;
						elseif ($c == 3)
							$green--;
						elseif ($c == 4)
							$red--;
						
						$cc++;
						if ($cc > 2) {
							$c++;
							$cc = 0;
						}
						if ($c > 4)
							$c = 0;
						
						if ($red > 3)
							$red = 0;
						if ($green > 3)
							$green = 0;
						if ($blue > 3)
							$blue = 0;
					}
					$options[] = 'HRULE:0#000000';
					
					if (!rrd_graph($settings['images'] . '/combined-' . $span . '.png', $options))
							error('RRDtool failed: ' . htmlentities(rrd_error()));
				}
			}
		}
	};
	
?>
