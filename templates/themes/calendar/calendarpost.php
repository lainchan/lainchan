<?php 
require '/var/www/html/inc/functions.php';

$method = $_SERVER['REQUEST_METHOD'];
	if ($method == 'POST') {
		// Method is POST
		$type = $_POST['type'];
		switch ($type) {
		case "add":
			$title = $_POST['title'];
			$description = $_POST['description'];
			$start = $_POST['start'];
			$end = $_POST['end'];
			$url = $_POST['url'];
			$color = $_POST['color'];
			$query = prepare("INSERT INTO calendar_events (title, description, start, end,url,color) VALUES (:title,:description, :start, :end,:url, :color )");
			$query->bindValue(':title', $title);
			$query->bindValue(':description', $description);
			$query->bindValue(':start', $start);
			$query->bindValue(':end', $end);
			$query->bindValue(':url', $url);
			$query->bindValue(':color', $color);
			$query->execute() or error(db_error($query));
			break;
		case "delete":
			$id = $_POST['id'];	
			$query = prepare("DELETE from calendar_events  WHERE id = :id");
			$query->bindValue(':id', $id);
			$query->execute() or error(db_error($query));
			break;
		case "update":
			$id = $_POST['id'];	
			$title = $_POST['title'];
			$description = $_POST['description'];
			$start = $_POST['start'];
			$end = $_POST['end'];
			$color = $_POST['color'];
			$url = $_POST['url'];
			$query = prepare(" UPDATE calendar_events SET title = :title, description = :description, start = :start, end = :end, url = :url, color =:color  WHERE id = :id");
			$query->bindValue(':id', $id);
			$query->bindValue(':title', $title);
			$query->bindValue(':description', $description);
			$query->bindValue(':start', $start);
			$query->bindValue(':end', $end);
			$query->bindValue(':url', $url);
			$query->bindValue(':color', $color);
			$query->execute() or error(db_error($query));
			break;
		default:
		}
	} elseif ($method == 'GET') {
		// Method is GET
		$query = query("SELECT * FROM calendar_events ORDER BY id") or error(db_error());
		 echo json_encode($query->fetchAll(PDO::FETCH_ASSOC));
	}

?>
