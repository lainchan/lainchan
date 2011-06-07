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
			global $config, $_theme;
			
			$this->boards = explode(' ', $settings['boards']);
			$this->spans = Array('hour', 'day', 'week', 'month');
			$this->interval = 60;
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
							'DS:posts:ABSOLUTE:120:0:100000000',
							'RRA:AVERAGE:0.5:1:2880',
							'RRA:AVERAGE:0.5:30:672',
							'RRA:AVERAGE:0.5:120:732',
							'RRA:AVERAGE:0.5:720:1460')))
								error('RRDtool failed: ' . htmlentities(rrd_error()));
					}
					
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
					
					foreach($this->spans as &$span) {
						// Graph graph
						if(!rrd_graph($settings['images'] . $board . '-' . $span . '.png', Array(
							'-s -1' . $span,
							'-t Posts on ' . sprintf($config['board_abbreviation'], $board),
							//'--lazy',
							'-l 0',
							'-h', $this->height, '-w', $this->width,
							'-l 0',
							'-a', 'PNG',
							'-W', 'Powered by Tinyboard',
							'-v posts/minute',
							'DEF:posts=' . $file . ':posts:AVERAGE',
							'LINE2:posts#336600:Posts',
							'GPRINT:posts:MAX:  Max\\: %5.1lf %s',
							'GPRINT:posts:AVERAGE: Avg\\: %5.1lf %s',
							'GPRINT:posts:LAST: Current\\: %5.1lf %sposts/min',
							'HRULE:0#000000')))
								error('RRDtool failed: ' . htmlentities(rrd_error()));
					}
				}
			}
		}
	};
	
?>