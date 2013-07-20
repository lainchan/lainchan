<?php
	require 'inc/functions.php';
	
	$queries_per_minutes = Array(15, 2);
	$queries_per_minutes_all = Array(50, 2);
	$search_limit = 100;
	
	$boards = Array('new', 'r9k', 'v', 'edu', 'azn', 'h', 'meta');
	
	$body = Element('search_form.html', Array('boards' => $boards, 'board' => isset($_POST['board']) ? $_POST['board'] : false, 'search' => isset($_POST['search']) ? str_replace('"', '&quot;', utf8tohtml($_POST['search'])) : false));
	
	if(isset($_POST['search']) && !empty($_POST['search']) && isset($_POST['board']) && in_array($_POST['board'], $boards)) {		
		$phrase = $_POST['search'];
		$_body = '';
		
		$query = prepare("SELECT COUNT(*) FROM `search_queries` WHERE `ip` = :ip AND `time` > :time");
		$query->bindValue(':ip', $_SERVER['REMOTE_ADDR']);
		$query->bindValue(':time', time() - ($queries_per_minutes[1] * 60));
		$query->execute() or error(db_error($query));
		if($query->fetchColumn() > $queries_per_minutes[0])
			error('Wait a while before searching again, please.');
		
		$query = prepare("SELECT COUNT(*) FROM `search_queries` WHERE `time` > :time");
		$query->bindValue(':time', time() - ($queries_per_minutes_all[1] * 60));
		$query->execute() or error(db_error($query));
		if($query->fetchColumn() > $queries_per_minutes_all[0])
			error('Wait a while before searching again, please.');
			
		
		$query = prepare("INSERT INTO `search_queries` VALUES (:ip, :time, :query)");
		$query->bindValue(':ip', $_SERVER['REMOTE_ADDR']);
		$query->bindValue(':time', time());
		$query->bindValue(':query', $phrase);
		$query->execute() or error(db_error($query));
		
		_syslog(LOG_NOTICE, 'Searched /' . $_POST['board'] . '/ for "' . $phrase . '"');
		
		openBoard($_POST['board']);
		
		$filters = Array();
		
		function search_filters($m) {
			global $filters;
			$name = $m[2];
			$value = isset($m[4]) ? $m[4] : $m[3];
			
			if(!in_array($name, Array('id', 'thread', 'subject', 'name'))) {
				// unknown filter
				return $m[0];
			}
			
			$filters[$name] = $value;
			
			return $m[1];
		}
		
		$phrase = trim(preg_replace_callback('/(^|\s)(\w+):("(.*)?"|[^\s]*)/', 'search_filters', $phrase));
		
		if(!preg_match('/[^*^\s]/', $phrase) && empty($filters)) {
			_syslog(LOG_WARNING, 'Query too broad.');
			$body .= '<p class="unimportant" style="text-align:center">(Query too broad.)</p>';
			echo Element('page.html', Array(
				'config'=>$config,
				'title'=>'Search',
				'body'=>$body,
			));
			exit;
		}
		
		// Escape escape character
		$phrase = str_replace('!', '!!', $phrase);
		
		// Remove SQL wildcard
		$phrase = str_replace('%', '!%', $phrase);
		
		// Use asterisk as wildcard to suit convention
		$phrase = str_replace('*', '%', $phrase);
		
		$like = '';
		$match = Array();
		
		// Find exact phrases
		if(preg_match_all('/"(.+?)"/', $phrase, $m)) {
			foreach($m[1] as &$quote) {
				$phrase = str_replace("\"{$quote}\"", '', $phrase);
				$match[] = $pdo->quote($quote);
			}
		}
		
		$words = explode(' ', $phrase);
		foreach($words as &$word) {
			if(empty($word))
				continue;
			$match[] = $pdo->quote($word);
		}
		
		$like = '';
		foreach($match as &$phrase) {
			if(!empty($like))
				$like .= ' AND ';
			$phrase = preg_replace('/^\'(.+)\'$/', '\'%$1%\'', $phrase);
			$like .= '`body` LIKE ' . $phrase . ' ESCAPE \'!\'';
		}
		
		foreach($filters as $name => $value) {
			if(!empty($like))
				$like .= ' AND ';
			$like .= '`' . $name . '` = '. $pdo->quote($value);
		}
		
		$like = str_replace('%', '%%', $like);
			
		$query = prepare(sprintf("SELECT * FROM `posts_%s` WHERE " . $like . " ORDER BY `time` DESC LIMIT :limit", $board['uri']));
		$query->bindValue(':limit', $search_limit, PDO::PARAM_INT);
		$query->execute() or error(db_error($query));
		
		if($query->rowCount() == $search_limit) {
			_syslog(LOG_WARNING, 'Query too broad.');
			$body .= '<p class="unimportant" style="text-align:center">(Query too broad.)</p>';
			echo Element('page.html', Array(
				'config'=>$config,
				'title'=>'Search',
				'body'=>$body,
			));
			exit;
		}

		$temp = '';
		while($post = $query->fetch()) {
			if(!$post['thread']) {
				$po = new Thread($post['id'], $post['subject'], $post['email'], $post['name'], $post['trip'], $post['capcode'], $post['body'], $post['time'], $post['thumb'], $post['thumbwidth'], $post['thumbheight'], $post['file'], $post['filewidth'], $post['fileheight'], $post['filesize'], $post['filename'], $post['ip'], $post['sticky'], $post['locked'], $post['sage'], $post['embed']);
			} else {
				$po = new Post($post['id'], $post['thread'], $post['subject'], $post['email'], $post['name'], $post['trip'], $post['capcode'], $post['body'], $post['time'], $post['thumb'], $post['thumbwidth'], $post['thumbheight'], $post['file'], $post['filewidth'], $post['fileheight'], $post['filesize'], $post['filename'], $post['ip'], $post['embed']);
			}
			$temp .= $po->build(true) . '<hr/>';
		}
		
		if(!empty($temp))
			$_body .= '<fieldset><legend>' . $query->rowCount() . ' result' . ($query->rowCount() != 1 ? 's' : '') . ' in <a href="/' .
					sprintf($config['board_path'], $board['uri']) . $config['file_index'] .
			'">' .
			sprintf($config['board_abbreviation'], $board['uri']) . ' - ' . $board['title'] .
			'</a></legend>' . $temp . '</fieldset>';
		
		$body .= '<hr/>';
		if(!empty($_body))
			$body .= $_body;
		else
			$body .= '<p style="text-align:center" class="unimportant">(No results.)</p>';
	}
		
	echo Element('page.html', Array(
		'config'=>$config,
		'title'=>'Search',
		'body'=>'' . $body
	));
