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
			$this->interval = 60;
			
			if($action == 'cron') {
				if(!file_exists($settings['path']))
					mkdir($settings['path']);
				
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
					
					if($action == 'cron') {
						$query = prepare(sprintf("SELECT COUNT(*) AS `count` FROM `posts_%s` WHERE `time` >= :time", $board));
						$query->bindValue(':time', time() - $this->interval, PDO::PARAM_INT):
						$query->exeucte() or error(db_error($query));
						$query->fetch();
						
						if(!rrd_update($file, Array(
								'-t',
								'posts',
								'N:')))
								error('RRDtool failed: ' . htmlentities(rrd_error()));
						}
				}
			}
		}
	};
	
?>