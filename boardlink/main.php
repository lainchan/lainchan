<?php
/*
 *  Copyright (c) 2014 Marcin Łabanowski
 */

if (isset ($brd) && !defined("TINYBOARD")) {
  define("BOARDLINK_IN", "callback");
  require_once("inc/functions.php");
}
else if (defined ("TINYBOARD")) {
  define("BOARDLINK_IN", "config");
}
else {
  die("Access denied");
}

// Version history (protocol compatibility):
// 1 - initial version
define("BOARDLINK_VERSION", "1");

$delete_from = false;

class BoardLink {
  function __construct($boardname, $self, $connected) {
    $this->boardname = $boardname;
    $this->self = $self;
    $this->connected = $connected;

    $this->queue = array();
  }

  function send_data($uri, $password, $data) {
    ignore_user_abort(true); // this is a critical code, we don't want users to desync boards
			     // by quitting at a random moment

    $d = http_build_query(array('password' => $password,
				   'from' => $this->self,
				   'version' => BOARDLINK_VERSION,
                                   'data' => serialize($data)));
    $ctx = stream_context_create(array('http' => array(
				         'method' => 'POST',
                                         'header' => "Content-type: application/x-www-form-urlencoded\r\n".
				                     "Content-length: ".strlen($d)."\r\n",
					 'content' => $d)));
    $this->queue[] = array($ctx, $uri, $data['action']);
  }

  function commit_send_as_last() {
    register_shutdown_function(array($this, "commit_send"));
  }

  function commit_send() {
    global $request_finished;

    if (function_exists("fastcgi_finish_request") && !isset($request_finished)) {
      fastcgi_finish_request(); // hooray! php-fpm!
      $request_finished = true;
    }

    foreach ($this->queue as $value) {
      list($ctx, $uri, $action) = $value;
      $fp = file_get_contents($uri.'callback.php', false, $ctx);

      _syslog(LOG_INFO, "BoardLink: sent query of type $action from {$this->self} to $uri. Query yielded $fp");
    }

    $this->queue = array();
  }

  function configure_board() {
    global $config;

    $config['blotter'] = "This board is synchronized with ";
    $a = array_values($this->connected);
    array_walk($a, function(&$v, $k){
        $v = "<a href=\"$v\">$v</a>";
    });
    $count = count($a);

    if ($count == 0) {
        $synced = '';
    } elseif ($count == 1) {
        $synced = $a[0];
    } else {
        $synced = implode(', ', array_slice($a,0,$count-1)) . ' and ' . end($a);
    }
    $config['blotter'] .= $synced;

    if ($config['vichan_federation']) {
      if ($config['locale'] != "en") {
        $config['locale'] = "en";
        $config['file_script'] = "main-en.js";
      }
      $config['country_flags'] = true;
    }

    event_handler('delete', function($post) {
      global $delete_from;

      $data = array();
      $data['action'] = 'delete';
      $data['post'] = $post;
      foreach ($this->connected as $password => $uri) {
	if (!$delete_from || $delete_from != $uri) {
	  $this->send_data($uri, $password, $data);
	}
      }
      register_shutdown_function(array($this, "commit_send_as_last"));
    });

    event_handler('post-after', function($post) {
      $data = array();
      $data['action'] = 'create';
      if (!isset ($post['ip'])) $post['ip'] = $_SERVER['REMOTE_ADDR'];
      $data['post'] = $post;
      foreach ($this->connected as $password => $uri) {
	if (!isset($data['post']['origin']) || $data['post']['origin'] != $uri) {
          $this->send_data($uri, $password, $data);
	}
      }
      register_shutdown_function(array($this, "commit_send_as_last"));
    });
  }

  function handle_error($err, $from, $sort) {
    _syslog(LOG_INFO, "BoardLink: received query of type $sort from $from to {$this->self}. Query finished with $err");
    die('{status:"'.$err.'",version:"'.BOARDLINK_VERSION.'"}');
  }

  function configure_callback() {
    global $board, $config, $delete_from, $build_pages, $pdo;

    if (!isset ($_POST['password'])) {
      $this->handle_error("ERR_NOREQUEST", "nil", "nil");
    }

    if (!isset ($this->connected[$_POST['password']])) {
      $this->handle_error("ERR_PASSWD", $_POST['from'], "nil");
    }
    $uri = $this->connected[$_POST['password']];
    if ($uri != $_POST['from']) {
      $this->handle_error("ERR_FROM", $_POST['from'], "nil");
    }
    $data = unserialize($_POST['data']);

    openBoard($this->boardname);

    switch ($data['action']) {
      case 'create':
        //Check if thread exists
        if (!$data['post']['op']) {
                $query = prepare(sprintf("SELECT `sticky`,`locked`,`sage` FROM ``posts_%s`` WHERE `id` = :id AND `thread` IS NULL LIMIT 1", $board['uri']));
                $query->bindValue(':id', $data['post']['thread'], PDO::PARAM_INT);
                $query->execute() or error(db_error());

                if (!$thread = $query->fetch(PDO::FETCH_ASSOC)) {
			$this->handle_error("ERR_DESYNC", $uri, $data['action']);
                }

                $numposts = numPosts($data['post']['thread']);
        }

        $a = array("src" => "file", "thumb" => "thumb");
        foreach ($a as $dir => $field) {
          if (isset($data['post'][$field]) && $data['post'][$field] &&
              $data['post'][$field] != 'spoiler' && $data['post'][$field] != 'deleted') {
	    // Security filename checks
	    if (preg_match('@\.php|\.phtml|\.ht|\.\.|\x00|/@i', $data['post'][$field])) {
	      $this->handle_error("ERR_SECURITY", $uri, $data['action']);
	    }
            $i = file_get_contents($uri.$dir.'/'.$data['post'][$field]);
            file_put_contents($this->boardname.'/'.$dir.'/'.$data['post'][$field], $i);
          }
        }
	$tmpid = post($data['post']);

	// Post doesn't cover custom post IDs
	$query = prepare(sprintf("UPDATE ``posts_%s`` SET `id`=:id WHERE `id`=:tmpid", $board['uri']));
	$query->bindValue("id", $id = $data['post']['id']);
	$query->bindValue("tmpid", $tmpid);
	if (!$query->execute()) {
		$query = prepare(sprintf("DELETE FROM ``posts_%s`` WHERE `id`=:tmpid", $board['uri']));
		$query->bindValue("tmpid", $tmpid);
		$query->execute();

		// Reset the auto increment
		query(sprintf("ALTER TABLE ``posts_%s`` AUTO_INCREMENT = 1", $board['uri']));

		$this->handle_error("ERR_DUPLICATE_ID", $uri, $data['action']);		
	}

	// Reset the auto increment
	query(sprintf("ALTER TABLE ``posts_%s`` AUTO_INCREMENT = 1", $board['uri']));

	$post = &$data['post'];
	$post['origin'] = $uri;

	// The rest is just a copied code from post.php

        if (isset($post['tracked_cites']) && !empty($post['tracked_cites'])) {
                $insert_rows = array();
                foreach ($post['tracked_cites'] as $cite) {
                        $insert_rows[] = '(' .
                                $pdo->quote($board['uri']) . ', ' . (int)$id . ', ' .
                                $pdo->quote($cite[0]) . ', ' . (int)$cite[1] . ')';
                }
                query('INSERT INTO ``cites`` VALUES ' . implode(', ', $insert_rows)) or error(db_error());
        }

        if (!$post['op'] && strtolower($post['email']) != 'sage' && !$thread['sage'] && ($config['reply_limit'] == 0
			|| $numposts['replies']+1 < $config['reply_limit'])) {
                bumpThread($post['thread']);
        }

        buildThread($post['op'] ? $id : $post['thread']);
        
        if ($config['try_smarter'] && $post['op'])
                $build_pages = range(1, $config['max_pages']);

        if ($post['op'])
                clean();
                
        event('post-after', $post);
        
        buildIndex();

        if ($post['op'])
                rebuildThemes('post-thread', $board['uri']);
        else
                rebuildThemes('post', $board['uri']);

        break;

      case 'delete':
	$delete_from = $uri;
	deletePost($data['post']['id'], false);
	$delete_from = false;

        buildIndex();
        rebuildThemes('post-delete', $board['uri']);

        break;

      default:
        $this->handle_error("ERR_UNSUPPORTED", $uri, $data['action']);
    }

    $this->handle_error("OK", $uri, $data['action']);
  }
}
?>
