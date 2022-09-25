<?php
/*
 *  Copyright (c) 2016 vichan-devel
 */

defined('TINYBOARD') or exit;

function gen_msgid($board, $id) {
	global $config;

	$b = preg_replace("/[^0-9a-zA-Z$]/", 'x', $board);
	$salt = sha1($board . "|" . $id . "|" . $config['nntpchan']['salt']);
	$salt = substr($salt, 0, 7);
	$salt = base_convert($salt, 16, 36);

	return "<$b.$id.$salt@".$config['nntpchan']['domain'].">";
}


function gen_nntp($headers, $files) {
	$content = null;
 if ((is_countable($files) ? count($files) : 0) == 0) {
	}
	else if ((is_countable($files) ? count($files) : 0) == 1 && $files[0]['type'] == 'text/plain') {
		$content = $files[0]['text'] . "\r\n";
		$headers['Content-Type'] = "text/plain; charset=UTF-8";
	}
	else {
		$boundary = sha1((string) $headers['Message-Id']);
		$content = "";
		$headers['Content-Type'] = "multipart/mixed; boundary=$boundary";
		foreach ($files as $file) {
			$content .= "--$boundary\r\n";
			if (isset($file['name'])) {
				$file['name'] = preg_replace('/[\r\n\0"]/', '', $file['name']);
				$content .= "Content-Disposition: form-data; filename=\"$file[name]\"; name=\"attachment\"\r\n";
			}
			$type = explode('/', (string) $file['type'])[0];
			if ($type == 'text') {
				$file['type'] .= '; charset=UTF-8';
			}
			$content .= "Content-Type: $file[type]\r\n";
			if ($type != 'text' && $type != 'message') {
				$file['text'] = base64_encode((string) $file['text']);
				$content .= "Content-Transfer-Encoding: base64\r\n";
			}
			$content .= "\r\n";
			$content .= $file['text'];
			$content .= "\r\n";
		}
		$content .= "--$boundary--\r\n";

		$headers['Mime-Version'] = '1.0';
	}
	//$headers['Content-Length'] = strlen($content);
	$headers['Date'] = date('r', $headers['Date']);
	$out = "";
	foreach ($headers as $id => $val) {
		$val = str_replace("\n", "\n\t", (string) $val);
		$out .= "$id: $val\r\n";
	}
	$out .= "\r\n";
	$out .= $content;
	return $out;
}

function nntp_publish($msg, $id) {
	global $config;
	$server = $config["nntpchan"]["server"];
	$s = fsockopen("tcp://$server");
	fgets($s);
	fputs($s, "MODE STREAM\r\n");
	fgets($s);
	fputs($s, "TAKETHIS $id\r\n");
	fputs($s, (string) $msg);
	fputs($s, "\r\n.\r\n");
	fgets($s);
	fputs($s, "QUIT\r\n");
	fclose($s);
}

function post2nntp($post, $msgid) {
	global $config;

	$headers = [];
	$files = [];

	$headers['Message-Id'] = $msgid;
	$headers['Newsgroups'] = $config['nntpchan']['group'];
	$headers['Date'] = time();
	$headers['Subject'] = $post['subject'] ?: "None";
	$headers['From'] = $post['name'] . " <poster@" . $config['nntpchan']['domain'] . ">";

	if ($post['email'] == 'sage') {
		$headers['X-Sage'] = true;
	}

	if (!$post['op']) {
		// Get muh parent
		$query = prepare("SELECT `message_id` FROM ``nntp_references`` WHERE `board` = :board AND `id` = :id");
		$query->bindValue(':board', $post['board']);
		$query->bindValue(':id', $post['thread']);
		$query->execute() or error(db_error($query));

		if ($result = $query->fetch(PDO::FETCH_ASSOC)) {
			$headers['References'] = $result['message_id'];
		}
		else {
			return false; // We don't have OP. Discarding.
		}
        }

	// Let's parse the body a bit.
	$body = trim((string) $post['body_nomarkup']);
	$body = preg_replace('/\r?\n/', "\r\n", $body);
	$body = preg_replace_callback('@>>(>/([a-zA-Z0-9_+-]+)/)?([0-9]+)@', function($o) use ($post) {
		if ($o[1]) {
			$board = $o[2];
		}
		else {
			$board = $post['board'];
		}
		$id = $o[3];

		$query = prepare("SELECT `message_id_digest` FROM ``nntp_references`` WHERE `board` = :board AND `id` = :id");
                $query->bindValue(':board', $board);
                $query->bindValue(':id', $id);
                $query->execute() or error(db_error($query));

                if ($result = $query->fetch(PDO::FETCH_ASSOC)) {
			return ">>".substr((string) $result['message_id_digest'], 0, 18);
		}
		else {
			return $o[0]; // Should send URL imo
		}
	}, $body);
	$body = preg_replace('/>>>>([0-9a-fA-F])+/', '>>\1', $body);


	$files[] = ['type' => 'text/plain', 'text' => $body];

	foreach ($post['files'] as $id => $file) {
		$fc = [];

		$fc['type'] = $file['type'];
		$fc['text'] = file_get_contents($file['file_path']);
		$fc['name'] = $file['name'];

		$files[] = $fc;
	}

	return [$headers, $files];
}
