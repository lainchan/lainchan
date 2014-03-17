<?php
	require 'info.php';
	
	function favelog_build($action, $settings, $board) {
		global $config;
		
		// Possible values for $action:
		//	- all (rebuild everything, initialization)
		//	- news (news has been updated)
		//	- boards (board list changed)
		//	- post (a reply has been made)
		//	- post-thread (a thread has been made)
		
		$boards = explode(' ', $settings['boards']);
				
		if ($action == 'all') {
			copy('templates/themes/favelog/catalog.css', 'stylesheets/' . $settings['css']);

			if($settings['use_tooltipster']) {
				copy('templates/themes/favelog/tooltipster.css', 'stylesheets/tooltipster.css');
				copy('templates/themes/favelog/jquery.tooltipster.min.js', 'js/jquery.tooltipster.min.js');
			}

			copy('templates/themes/favelog/jquery.mixitup.min.js', 'js/jquery.mixitup.min.js');
			copy('templates/themes/favelog/favelog.js', 'js/favelog.js');
			
			foreach ($boards as $board) {
				$b = new Favelog();
				$b->build($settings, $board);
			}
		} elseif ($action == 'post-thread' || ($settings['update_on_posts'] && $action == 'post') || ($settings['update_on_posts'] && $action == 'post-delete') && in_array($board, $boards)) {
			$b = new Favelog();
			$b->build($settings, $board);
		}
	}
	
	// Wrap functions in a class so they don't interfere with normal Tinyboard operations
	class Favelog {
		public function build($settings, $board_name) {
			global $config, $board;
			
			openBoard($board_name);
			
			$recent_images = array();
			$recent_posts = array();
			$stats = array();
			
			$query = query(sprintf("SELECT *, `id` AS `thread_id`, (SELECT COUNT(*) FROM ``posts_%s`` WHERE `thread` = `thread_id`) AS `reply_count`, (SELECT COUNT(*) FROM ``posts_%s`` WHERE `thread` = `thread_id` AND `filesize` IS NOT NULL) AS `image_count`, (SELECT `time` FROM ``posts_%s`` WHERE `thread` = `thread_id` ORDER BY `time` DESC LIMIT 1) AS `last_reply`, (SELECT `name` FROM ``posts_%s`` WHERE `thread` = `thread_id` ORDER BY `time` DESC LIMIT 1) AS `last_reply_name`, (SELECT `subject` FROM ``posts_%s`` WHERE `thread` = `thread_id` ORDER BY `time` DESC LIMIT 1) AS `last_reply_subject`, '%s' AS `board` FROM ``posts_%s`` WHERE `thread` IS NULL ORDER BY `bump` DESC", $board_name, $board_name, $board_name, $board_name, $board_name, $board_name, $board_name)) or error(db_error());
			
			while ($post = $query->fetch(PDO::FETCH_ASSOC)) {
				$post['link'] = $config['root'] . $board['dir'] . $config['dir']['res'] . sprintf($config['file_page'], ($post['thread'] ? $post['thread'] : $post['id']));
				$post['board_name'] = $board['name'];
				$post['file'] = $config['uri_thumb'] . $post['thumb'];
				
				if ($settings['use_tooltipster']) {
					$post['muhdifference'] = $this->getDiferenca($post['time']);
					
					if ($post['last_reply']) 
						$post['last_reply_difference'] = $this->getDiferenca($post['last_reply']);
				}

				$recent_posts[] = $post;
			}
			
			file_write($config['dir']['home'] . $board_name . '/catalog.html', Element('themes/favelog/catalog.html', Array(
				'settings' => $settings,
				'config' => $config,
				'boardlist' => createBoardlist(),
				'recent_images' => $recent_images,
				'recent_posts' => $recent_posts,
				'stats' => $stats,
				'board' => $board_name,
				'link' => $config['root'] . $board['dir']
			)));
		}

		public function getDiferenca($muhtime)
		{
			$postagem = new DateTime(date("Y-m-d H:i:s", $muhtime));
			$agora = new DateTime(date("Y-m-d H:i:s"));
			$intervalo = $postagem->diff($agora);
			$anos = $intervalo->format('%y');
			$meses = $intervalo->format('%m');
			$dias = $intervalo->format('%d');
			$horas = $intervalo->format('%h');
			$minutos = $intervalo->format('%i');
			$segundos = $intervalo->format('%s');

			$diferenca = ""; // adicionar o 'há' depois
			if ($anos) {
				if ($meses) {
					$diferenca.= ($anos>1)? $anos.' anos e ' : $anos.' ano e ';
					$diferenca.= ($meses>1)? $meses.' meses.' : $meses.' mês.';
				} else {
					$diferenca.= ($anos>1)? $anos.' anos.' : $anos.' ano.';
				}
			} elseif($meses) {
				if ($dias) {
					$diferenca.= ($meses>1)? $meses.' meses e ' : $meses.' mês e ';
					$diferenca.= ($dias>1)? $dias.' dias.' : $dias.' dia.';
				} else {
					$diferenca.= ($meses>1)? $meses.' meses.' : $meses.' mês.';
				}
			} elseif ($dias) {
				if ($horas) {
					$diferenca.= ($dias>1)? $dias.' dias e ' : $dias.' dia e ';
					$diferenca.= ($horas>1)? $horas.' horas.' : $horas.' hora.';
				} else {
					$diferenca.= ($dias>1)? $dias.' dias.' : $dias.' dia.';
				}
			} elseif ($horas) {
				if ($minutos) {
					$diferenca.= ($horas>1)? $horas.' horas e ' : $horas.' hora e ';
					$diferenca.= ($minutos>1)? $minutos.' minutos.' : $minutos.' minuto.';
				} else {
					$diferenca.= ($horas>1)? $horas.' horas.' : $horas.' hora.';
				}
			} elseif ($minutos) {
				if ($segundos) {
					$diferenca.= ($minutos>1)? $minutos.' minutos e ' : $minutos.' minuto e ';
					$diferenca.= ($segundos>1)? $segundos.' segundos.' : $segundos.' segundo.';
				} else {
					$diferenca.= ($minutos>1)? $minutos.' minutos.' : $minutos.' minuto.';
				}
			} elseif ($segundos) {
				$diferenca.= ($segundos>1)? $segundos.' segundos.' : $segundos.' segundo.';
			}

			if (!empty($diferenca))
				$diferenca = 'há '.$diferenca;
			return $diferenca;
		}
	};
