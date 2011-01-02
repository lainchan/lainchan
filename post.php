<?php
	require 'inc/functions.php';
	require 'inc/display.php';
	if (file_exists('inc/instance-config.php')) {
		require 'inc/instance-config.php';
	}
	require 'inc/config.php';
	require 'inc/template.php';
	require 'inc/database.php';
	require 'inc/user.php';
	
	// Fix for magic quotes
	if (get_magic_quotes_gpc()) {
		function strip_array($var) {
			return is_array($var) ? array_map("strip_array", $var) : stripslashes($var);
		}
		
		$_SESSION = strip_array($_SESSION);
		$_GET = strip_array($_GET);
		$_POST = strip_array($_POST);
	}
	
	if(isset($_POST['post'])) {
		if(	!isset($_POST['name']) ||
			!isset($_POST['email']) ||
			!isset($_POST['subject']) ||
			!isset($_POST['body']) ||
			!isset($_POST['board']) ||
			!isset($_POST['password'])
			) error(ERROR_BOT);
		
		$post = Array('board' => $_POST['board']);
		
		if(isset($_POST['thread'])) {
			$OP = false;
			$post['thread'] = round($_POST['thread']);
		} else $OP = true;
		
		if(!(($OP && $_POST['post'] == BUTTON_NEWTOPIC) ||
		    (!$OP && $_POST['post'] == BUTTON_REPLY)))
			error(ERROR_BOT);
		
		// Check the referrer
		if($OP) {
			if(!isset($_SERVER['HTTP_REFERER']) || !preg_match(URL_MATCH, $_SERVER['HTTP_REFERER'])) error(ERROR_BOT);
		}
		
		// TODO: Since we're now using static HTML files, we can't give them cookies on their first page view
		// Find another anti-spam method.
		
		/*
		// Check if he has a valid cookie.
		if(!$user['valid']) error(ERROR_BOT);
		
		// Check how long he has been here.
		if(time()-$user['appeared']<LURKTIME) error(ERROR_LURK);
		*/
		
		// Open database connection
		sql_open();
		
		// Check if banned
		checkBan();
		
		// Check if board exists
		if(!openBoard($post['board']))
			error(ERROR_NOBOARD);
		
		//Check if thread exists
		if(!$OP && !threadExists($post['thread']))
			error(ERROR_NONEXISTANT);
		
		// Check for a file
		if($OP) {
			if(!isset($_FILES['file']['tmp_name']) || empty($_FILES['file']['tmp_name']))
				error(ERROR_NOIMAGE);
		}
		
		$post['name'] = (!empty($_POST['name'])?$_POST['name']:'Anonymous');
		$post['subject'] = $_POST['subject'];
		$post['email'] = utf8tohtml($_POST['email']);
		$post['body'] = $_POST['body'];
		$post['password'] = $_POST['password'];
		$post['filename'] = $_FILES['file']['name'];
		$post['has_file'] = $OP || !empty($_FILES['file']['tmp_name']);
		$post['mod'] = isset($_POST['mod']) && $_POST['mod'];
		
		if($post['mod']) {
			require 'inc/mod.php';
			if(!$mod) {
				// Liar. You're not a mod.
				error(ERROR_NOTAMOD);
			}
		}
		
		if($post['has_file']) {
			$size = $_FILES['file']['size'];
			if($size > MAX_FILESIZE)
				error(sprintf3(ERR_FILESIZE, array(
					'sz'=>commaize($size),
					'filesz'=>commaize($size),
					'maxsz'=>commaize(MAX_FILESIZE))));
		}
		
		$trip = generate_tripcode($post['name']);
		$post['name'] = $trip[0];
		$post['trip'] = (isset($trip[1])?$trip[1]:'');
		
		if($post['email'] == 'noko') {
			$noko = true;
			$post['email'] = '';
		} else $noko = false;
		
		if($post['has_file']) {
			$post['extension'] = strtolower(substr($post['filename'], strrpos($post['filename'], '.') + 1));
			$post['file_id'] = rand(0, 1000000000);
			$post['file'] = $board['dir'] . DIR_IMG . $post['file_id'] . '.' . $post['extension'];
			$post['thumb'] = $board['dir'] . DIR_THUMB . $post['file_id'] . '.png';
			$post['zip'] = $OP && $post['has_file'] && ALLOW_ZIP && $post['extension'] == 'zip' ? $post['file'] : false;
			if(!($post['zip'] || in_array($post['extension'], $allowed_ext))) error(ERROR_FILEEXT);
		}
		
		// Check string lengths
		if(strlen($post['name']) > 25) error(sprintf(ERROR_TOOLONG, 'name'));
		if(strlen($post['email']) > 30) error(sprintf(ERROR_TOOLONG, 'email'));
		if(strlen($post['subject']) > 40) error(sprintf(ERROR_TOOLONG, 'subject'));
		if(strlen($post['body']) > MAX_BODY) error(ERROR_TOOLONGBODY);
		if(!(!$OP && $post['has_file']) && strlen($post['body']) < 1) error(ERROR_TOOSHORTBODY);
		if(strlen($post['password']) > 20) error(sprintf(ERROR_TOOLONG, 'password'));
		
		markup($post['body']);
		
		if($post['has_file']) {
			// Just trim the filename if it's too long
			if(strlen($post['filename']) > 30) $post['filename'] = substr($post['filename'], 0, 27).'…';
			// Move the uploaded file
			if(!@move_uploaded_file($_FILES['file']['tmp_name'], $post['file'])) error(ERROR_NOMOVE);
			
			if($post['zip']) {
				// Validate ZIP file
				if(is_resource($zip = zip_open($post['zip'])))
					// TODO: Check if it's not empty and has at least one (valid) image
					zip_close($zip);
				else
					error(ERR_INVALIDZIP);
				
				$post['file'] = ZIP_IMAGE;
				$post['extension'] = strtolower(substr($post['file'], strrpos($post['file'], '.') + 1));
			}
			
			$size = @getimagesize($post['file']);
			$post['width'] = $size[0];
			$post['height'] = $size[1];
			
			// Check if the image is valid
			if($post['width'] < 1 || $post['height'] < 1) {
				unlink($post['file']);
				error(ERR_INVALIDIMG);
			}
			
			if($post['width'] > MAX_WIDTH || $post['height'] > MAX_HEIGHT) {
				unlink($post['file']);
				error(ERR_MAXSIZE);
			}
			
			$post['filehash'] = md5_file($post['file']);
			$post['filesize'] = filesize($post['file']);
			
			$image = createimage($post['extension'], $post['file']);
			
			if(REDRAW_IMAGE && !$post['zip']) {
				switch($post['extension']) {
					case 'jpg':
					case 'jpeg':
						imagejpeg($image, $post['file'], JPEG_QUALITY);
						break;
					case 'png':
						imagepng($image, $post['file'], 7);
						break;
					case 'gif':
						if(REDRAW_GIF)
							imagegif($image, $post['file']);
						break;
					case 'bmp':
						imagebmp($image, $post['file']);
						break;
					default:
						error('Unknwon file extension.');
				}
			}
			
			// Create a thumbnail
			$thumb = resize($image, $post['width'], $post['height'], $post['thumb'], THUMB_WIDTH, THUMB_HEIGHT);
			
			$post['thumbwidth'] = $thumb['width'];
			$post['thumbheight'] = $thumb['height'];
		}
		
		// Remove DIR_* before inserting them into the database.
		if($post['has_file']) {
			$post['file'] = substr_replace($post['file'], '', 0, strlen($board['dir'] . DIR_IMG));
			$post['thumb'] = substr_replace($post['thumb'], '', 0, strlen($board['dir'] . DIR_THUMB));
		}
		
		// Todo: Validate some more, remove messy code, allow more specific configuration
		
		$id = post($post, $OP);
		
		if($post['has_file'] && $post['zip']) {
			// Open ZIP
			$zip = zip_open($post['zip']);
			// Read files
			while($entry = zip_read($zip)) {
				$filename = basename(zip_entry_name($entry));
				$extension = strtolower(substr($filename, strrpos($filename, '.') + 1));
				
				if(in_array($extension, $allowed_ext)) {
					  if (zip_entry_open($zip, $entry, 'r')) {
						// Fake post
						$dump_post = Array(
							'subject' => $post['subject'],
							'email' => $post['email'],
							'name' => $post['name'],
							'trip' => $post['trip'],
							'body' => '',
							'thread' => $id,
							'password' => '',
							'has_file' => true,
							'file_id' => rand(0, 1000000000),
							'filename' => $filename
						);
						
						$dump_post['file'] = $board['dir'] . DIR_IMG . $dump_post['file_id'] . '.' . $extension;
						$dump_post['thumb'] = $board['dir'] . DIR_THUMB . $dump_post['file_id'] . '.png';
						
						// Extract the image from the ZIP
						$fp = fopen($dump_post['file'], 'w+');
						fwrite($fp, zip_entry_read($entry, zip_entry_filesize($entry)));
						fclose($fp);
						
						$size = @getimagesize($dump_post['file']);
						$dump_post['width'] = $size[0];
						$dump_post['height'] = $size[1];
						
						// Check if the image is valid
						if($dump_post['width'] < 1 || $dump_post['height'] < 1) {
							unlink($dump_post['file']);
						} else {
							if($dump_post['width'] > MAX_WIDTH || $dump_post['height'] > MAX_HEIGHT) {
								unlink($dump_post['file']);
								error(ERR_MAXSIZE);
							} else {
								$dump_post['filehash'] = md5_file($dump_post['file']);
								$dump_post['filesize'] = filesize($dump_post['file']);
								
								$image = createimage($extension, $dump_post['file']);
								
								$success = true;
								if(REDRAW_IMAGE) {
									switch($extension) {
										case 'jpg':
										case 'jpeg':
											imagejpeg($image, $dump_post['file'], JPEG_QUALITY);
											break;
										case 'png':
											imagepng($image, $dump_post['file'], 7);
											break;
										case 'gif':
											if(REDRAW_GIF)
												imagegif($image, $dump_post['file']);
											break;
										case 'bmp':
											imagebmp($image, $dump_post['file']);
											break;
										default:
											$success = false;
									}
								}
						
						
								// Create a thumbnail
								$thumb = resize($image, $dump_post['width'], $dump_post['height'], $dump_post['thumb'], THUMB_WIDTH, THUMB_HEIGHT);
								
								$dump_post['thumbwidth'] = $thumb['width'];
								$dump_post['thumbheight'] = $thumb['height'];
								
								// Remove DIR_* before inserting them into the database.
								$dump_post['file'] = substr_replace($dump_post['file'], '', 0, strlen($board['dir'] . DIR_IMG));
								$dump_post['thumb'] = substr_replace($dump_post['thumb'], '', 0, strlen($board['dir'] . DIR_THUMB));
								
								// Create the post
								post($dump_post, false);
							}
						}
						
						// Close the ZIP
						zip_entry_close($entry);
					}
				}
			}
			zip_close($zip);
			unlink($post['zip']);
		}
		
		buildThread(($OP?$id:$post['thread']));
		
		if(!$OP) {
			bumpThread($post['thread']);
		}
		
		buildIndex();
		sql_close();
		
		if(ALWAYS_NOKO || $noko) {
			header('Location: ' . ROOT . $board['dir'] . DIR_RES . ($OP?$id:$post['thread']) . '.html' . (!$OP?'#'.$id:''), true, REDIRECT_HTTP);
		} else {
			header('Location: ' . ROOT . $board['dir'] . FILE_INDEX, true, REDIRECT_HTTP);
		}
		
		exit;
	} else {
		if(!file_exists(HAS_INSTALLED)) {
			sql_open();
			
			// Build all boards
			$boards = listBoards();
			foreach($boards as &$_board) {
				setupBoard($_board);
				buildIndex();
			}
			
			sql_close();
			touch(HAS_INSTALLED, 0777);
			
			die(Element('page.html', Array(
				'index'=>ROOT,
				'title'=>'Success',
				'body'=>"<center>" .
						"<h2>Tinyboard is now installed!</h2>" .
						"</center>"
			)));
		} else {
			// They opened post.php in their browser manually.
			// Possible TODO: Redirect back to homepage.
			error(ERROR_NOPOST);
		}
	}
?>

