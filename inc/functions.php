<?php
	function sprintf3($str, $vars, $delim = '%') {
		$replaces = array();
		foreach($vars as $k => $v) {
			$replaces[$delim . $k . $delim] = $v;
		}
		return str_replace(array_keys($replaces),
		                   array_values($replaces), $str);
	}

	function sql_open() {
		global $sql;
		$sql = @mysql_connect(MY_SERVER, MY_USER, MY_PASSWORD) or error('Database error.');
		@mysql_select_db(MY_DATABASE, $sql) or error('Database error.');
	}
	function sql_close() {
		global $sql;
		@mysql_close($sql);
	}
	
	function mysql_safe_array(&$array) {
		foreach($array as &$item) {
			$item = mysql_real_escape_string($item);
		}
	}
	
	function index($page) {
		global $sql, $board;
		
		$body = '';
		$offset = round($page*THREADS_PER_PAGE-THREADS_PER_PAGE);
		
		sql_open();
		$query = mysql_query('SELECT * FROM `posts` WHERE `thread` IS NULL ORDER BY `bump` DESC LIMIT ' . $offset . ',' . THREADS_PER_PAGE, $sql) or error(mysql_error($sql));
		if(mysql_num_rows($query) < 1 && $page > 1) return false;
		while($th = mysql_fetch_array($query)) {
			$thread = new Thread($th['id'], $th['subject'], $th['email'], $th['name'], $th['trip'], $th['body'], $th['time'], $th['thumb'], $th['thumbwidth'], $th['thumbheight'], $th['file'], $th['filewidth'], $th['fileheight'], $th['filesize'], $th['filename']);
			
			$newposts = mysql_query(sprintf(
					"SELECT `id`, `subject`, `email`, `name`, `trip`, `body`, `time`, `thumb`, `thumbwidth`, `thumbheight`, `file`, `filewidth`, `fileheight`, `filesize`, `filename` FROM `posts` WHERE `thread` = '%s' ORDER BY `time` DESC LIMIT %d",
					$th['id'],
					THREADS_PREVIEW
				), $sql) or error(mysql_error($sql));
			if(mysql_num_rows($newposts) == THREADS_PREVIEW) {
				$count_query = mysql_query(sprintf(
					"SELECT COUNT(`id`) as `num` FROM `posts` WHERE `thread` = '%s'",
					$th['id']
				), $sql) or error(mysql_error($sql));
				$count = mysql_fetch_array($count_query);
				$omitted = $count['num'] - THREADS_PREVIEW;
				$thread->omitted = $omitted;
				mysql_free_result($count_query);
				unset($count);
				unset($omitted);
			}
			while($po = mysql_fetch_array($newposts)) {
				$thread->add(new Post($po['id'], $th['id'], $po['subject'], $po['email'], $po['name'], $po['trip'], $po['body'], $po['time'], $po['thumb'], $po['thumbwidth'], $po['thumbheight'], $po['file'], $po['filewidth'], $po['fileheight'], $po['filesize'], $po['filename']));
			}
			mysql_free_result($newposts);
			
			$thread->posts = array_reverse($thread->posts);
			$body .= $thread->build(true);
		}
		mysql_free_result($query);
		return Array('button'=>BUTTON_NEWTOPIC, 'board'=>$board, 'body'=>$body, 'post_url' => POST_URL, 'index' => ROOT);
	}
	
	function buildIndex() {
		global $sql;
		sql_open();
		
		$res = mysql_query("SELECT COUNT(`id`) as `num` FROM `posts` WHERE `thread` IS NULL", $sql) or error(mysql_error($sql));
		$arr = mysql_fetch_array($res);
		$count = floor((THREADS_PER_PAGE + $arr['num'] - 1) / THREADS_PER_PAGE);
		
		$pages = Array();
		for($x=0;$x<$count && $x<MAX_PAGES;$x++) {
			$pages[] = Array('num' => $x+1, 'link' => $x==0 ? ROOT . FILE_INDEX : ROOT . sprintf(FILE_PAGE, $x+1));
		}		
		
		mysql_free_result($res);
		unset($arr);
		unset($count);
		
		$page = 1;
		while($page <= MAX_PAGES && $content = index($page)) {
			$filename = $page==1 ? FILE_INDEX : sprintf(FILE_PAGE, $page);
			if(file_exists($filename)) $md5 = md5_file($filename);
			
			$content['pages'] = $pages;
			@file_put_contents($filename, Element('index.html', $content)) or error("Couldn't write to file.");
			
			if(isset($md5) && $md5 == md5_file($filename)) {
				break;
			}
			$page++;
		}
		if($page < MAX_PAGES) {
			for(;$page<=MAX_PAGES;$page++) {
				$filename = $page==1 ? FILE_INDEX : sprintf(FILE_PAGE, $page);
				@unlink($filename);
			}
		}
	}
	
	function markup(&$body) {
		global $sql;
		
		if(AUTO_UNICODE) {
			$body = str_replace('...', '…', $body);
			$body = str_replace('<--', '←', $body);
			$body = str_replace('--', '—', $body);
		}
		
		$body = utf8tohtml($body, true);
		
		// Cites
		if(preg_match_all('/(^|\s)&gt;&gt;([0-9]+?)(\s|$)/', $body, $cites)) {
			$previousPosition = 0;
			$temp = '';
			sql_open();
			for($index=0;$index<count($cites[0]);$index++) {
				$cite = $cites[2][$index];
				$whitespace = Array(
					strlen($cites[1][$index]),
					strlen($cites[3][$index]),
				);
				
				$result = mysql_query(sprintf("SELECT `thread`,`id` FROM `posts` WHERE `id` = '%d'", $cite), $sql);
				if($post = mysql_fetch_array($result)) {
					$replacement = '<a onclick="highlightReply(\''.$cite.'\');" href="' . ROOT . DIR_RES . ($post['thread']?$post['thread']:$post['id']) . '.html#' . $cite . '">&gt;&gt;' . $cite . '</a>';
				} else {
					$replacement = "&gt;&gt;{$cite}";
				}
				mysql_free_result($result);
				
				// Find the position of the cite
				$position = strpos($body, $cites[0][$index]);
				// Replace the found string with "xxxx[...]". (allows duplicate tags). Keeps whitespace.
				$body = substr_replace($body, str_repeat('x', strlen($cites[0][$index]) - $whitespace[0] - $whitespace[1]), $position + $whitespace[0], strlen($cites[0][$index]) - $whitespace[0] - $whitespace[1]);
				
				$temp .= substr($body, $previousPosition, $position-$previousPosition) . $cites[1][$index] . $replacement . $cites[3][$index];
				$previousPosition = $position+strlen($cites[0][$index]);
			}
			// The rest
			$temp .= substr($body, $previousPosition);
			
			$body = $temp;
		}
		
		$body = str_replace("\r", '', $body);
		
		if(MARKUP_URLS)
			$body = preg_replace(URL_REGEX, "<a href=\"$0\">$0</a>", $body);
			
		$body = preg_replace("/(^|\n)([\s]+)?(&gt;)([^\n]+)?($|\n)/m", '$1$2<span class="quote">$3$4</span>$5', $body);
		$body = preg_replace("/(^|\n)==(.+?)==\n?/m", "<h2>$2</h2>", $body);
		$body = preg_replace("/'''(.+?)'''/m", "<strong>$1</strong>", $body);
		$body = preg_replace("/\n/", '<br/>', $body);
	}
	
	function utf8tohtml($utf8, $encodeTags=true) {
		$result = '';
		for ($i = 0; $i < strlen($utf8); $i++) {
			$char = $utf8[$i];
			$ascii = ord($char);
			if ($ascii < 128) {
				// one-byte character
				$result .= ($encodeTags) ? htmlentities($char) : $char;
			} else if ($ascii < 192) {
				// non-utf8 character or not a start byte
			} else if ($ascii < 224) {
				// two-byte character
				$result .= htmlentities(substr($utf8, $i, 2), ENT_QUOTES, 'UTF-8');
				$i++;
			} else if ($ascii < 240) {
				// three-byte character
				$ascii1 = ord($utf8[$i+1]);
				$ascii2 = ord($utf8[$i+2]);
				$unicode = (15 & $ascii) * 4096 +
						   (63 & $ascii1) * 64 +
						   (63 & $ascii2);
				$result .= "&#$unicode;";
				$i += 2;
			} else if ($ascii < 248) {
				// four-byte character
				$ascii1 = ord($utf8[$i+1]);
				$ascii2 = ord($utf8[$i+2]);
				$ascii3 = ord($utf8[$i+3]);
				$unicode = (15 & $ascii) * 262144 +
						   (63 & $ascii1) * 4096 +
						   (63 & $ascii2) * 64 +
						   (63 & $ascii3);
				$result .= "&#$unicode;";
				$i += 3;
			}
		}
		return $result;
	}
	
	function buildThread($id) {
		global $sql, $board;
		$id = round($id);
		
		$query = mysql_query(sprintf(
				"SELECT `id`,`thread`,`subject`,`name`,`email`,`trip`,`body`,`time`,`thumb`,`thumbwidth`,`thumbheight`,`file`,`filewidth`,`fileheight`,`filesize`,`filename` FROM `posts` WHERE (`thread` IS NULL AND `id` = '%s') OR `thread` = '%s' ORDER BY `thread`,`time`",
				$id,
				$id
			), $sql) or error(mysql_error($sql));
		
		while($post = mysql_fetch_array($query)) {
			if(!isset($thread)) {
				$thread = new Thread($post['id'], $post['subject'], $post['email'], $post['name'], $post['trip'], $post['body'], $post['time'], $post['thumb'], $post['thumbwidth'], $post['thumbheight'], $post['file'], $post['filewidth'], $post['fileheight'], $post['filesize'], $post['filename'], false);
			} else {
				$thread->add(new Post($post['id'], $thread->id, $post['subject'], $post['email'], $post['name'], $post['trip'], $post['body'], $post['time'], $post['thumb'], $post['thumbwidth'], $post['thumbheight'], $post['file'], $post['filewidth'], $post['fileheight'], $post['filesize'], $post['filename']));
			}
			@file_put_contents(DIR_RES . $id . '.html', Element('thread.html', Array('button'=>BUTTON_REPLY, 'board'=>$board, 'body'=>$thread->build(), 'post_url' => POST_URL, 'index' => ROOT, 'id' => $id))) or error("Couldn't write to file.");
		}
		mysql_free_result($query);
	}
	
	// A lot of the bellow of from BBSchan (An old project by savetheinternet)
	function generate_tripcode ( $name, $length = 10 ) {
		$name = stripslashes ( $name );
		$t = explode('#', $name);
		$nameo = $t[0];
		if ( isset ( $t[1] ) || isset ( $t[2] ) ) {
			$trip = ( ( strlen ( $t[1] ) > 0 ) ? $t[1] : $t[2] );
			if ( ( function_exists ( 'mb_convert_encoding' ) ) ) {
				# mb_substitute_character('none');
				$recoded_cap = mb_convert_encoding ( $trip, 'Shift_JIS', 'UTF-8' );
			}
			$trip = ( ( ! empty ( $recoded_cap ) ) ? $recoded_cap : $trip );
			$salt = substr ( $trip.'H.', 1, 2 );
			$salt = preg_replace ( '/[^\.-z]/', '.', $salt );
			$salt = strtr ( $salt, ':;<=>?@[\]^_`', 'ABCDEFGabcdef' );
			if ( isset ( $t[2] ) ) {
				// secure
				$trip = '!!' . substr ( crypt ( $trip, '@#$%^&*()' ), ( -1 * $length ) );
			} else {
				// insecure
				$trip = '!' . substr ( crypt ( $trip, $salt ), ( -1 * $length ) );
			}
		}
		if ( isset ( $trip ) ) {
			return array ( $nameo, $trip );
		} else {
			return array ( $nameo );
		}
	}
	
	// Highest common factor
	function hcf($a, $b){
		$gcd = 1;
		if ($a>$b) {
			$a = $a+$b;
			$b = $a-$b;
			$a = $a-$b;
		}
		if ($b==(round($b/$a))*$a) 
			$gcd=$a;
		else {
			for($i=round($a/2);$i;$i--) {
				if ($a == round($a/$i)*$i && $b == round($b/$i)*$i) {
					$gcd = $i;
					$i = false;
				}
			}
		}
		return $gcd;
	}

	function fraction($numerator, $denominator, $sep) {
		$gcf = hcf($numerator, $denominator);
		$numerator = $numerator / $gcf;
		$denominator = $denominator / $gcf;
		
		return "{$numerator}{$sep}{$denominator}";
	}

	/*********************************************/
	/* Fonction: imagecreatefrombmp              */
	/* Author:   DHKold                          */
	/* Contact:  admin@dhkold.com                */
	/* Date:     The 15th of June 2005           */
	/* Version:  2.0B                            */
	/*********************************************/

	function imagecreatefrombmp($filename) {
	   if (! $f1 = fopen($filename,"rb")) return FALSE;
	   $FILE = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1,14));
	   if ($FILE['file_type'] != 19778) return FALSE;
	   $BMP = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel'.
					 '/Vcompression/Vsize_bitmap/Vhoriz_resolution'.
					 '/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1,40));
	   $BMP['colors'] = pow(2,$BMP['bits_per_pixel']);
	   if ($BMP['size_bitmap'] == 0) $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
	   $BMP['bytes_per_pixel'] = $BMP['bits_per_pixel']/8;
	   $BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']);
	   $BMP['decal'] = ($BMP['width']*$BMP['bytes_per_pixel']/4);
	   $BMP['decal'] -= floor($BMP['width']*$BMP['bytes_per_pixel']/4);
	   $BMP['decal'] = 4-(4*$BMP['decal']);
	   if ($BMP['decal'] == 4) $BMP['decal'] = 0;
	   
	   $PALETTE = array();
	   if ($BMP['colors'] < 16777216)
	   {
		$PALETTE = unpack('V'.$BMP['colors'], fread($f1,$BMP['colors']*4));
	   }
	   
	   $IMG = fread($f1,$BMP['size_bitmap']);
	   $VIDE = chr(0);

	   $res = imagecreatetruecolor($BMP['width'],$BMP['height']);
	   $P = 0;
	   $Y = $BMP['height']-1;
	   while ($Y >= 0)
	   {
		$X=0;
		while ($X < $BMP['width'])
		{
		 if ($BMP['bits_per_pixel'] == 24)
			$COLOR = unpack("V",substr($IMG,$P,3).$VIDE);
		 elseif ($BMP['bits_per_pixel'] == 16)
		 {  
			$COLOR = unpack("n",substr($IMG,$P,2));
			$COLOR[1] = $PALETTE[$COLOR[1]+1];
		 }
		 elseif ($BMP['bits_per_pixel'] == 8)
		 {  
			$COLOR = unpack("n",$VIDE.substr($IMG,$P,1));
			$COLOR[1] = $PALETTE[$COLOR[1]+1];
		 }
		 elseif ($BMP['bits_per_pixel'] == 4)
		 {
			$COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
			if (($P*2)%2 == 0) $COLOR[1] = ($COLOR[1] >> 4) ; else $COLOR[1] = ($COLOR[1] & 0x0F);
			$COLOR[1] = $PALETTE[$COLOR[1]+1];
		 }
		 elseif ($BMP['bits_per_pixel'] == 1)
		 {
			$COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
			if     (($P*8)%8 == 0) $COLOR[1] =  $COLOR[1]        >>7;
			elseif (($P*8)%8 == 1) $COLOR[1] = ($COLOR[1] & 0x40)>>6;
			elseif (($P*8)%8 == 2) $COLOR[1] = ($COLOR[1] & 0x20)>>5;
			elseif (($P*8)%8 == 3) $COLOR[1] = ($COLOR[1] & 0x10)>>4;
			elseif (($P*8)%8 == 4) $COLOR[1] = ($COLOR[1] & 0x8)>>3;
			elseif (($P*8)%8 == 5) $COLOR[1] = ($COLOR[1] & 0x4)>>2;
			elseif (($P*8)%8 == 6) $COLOR[1] = ($COLOR[1] & 0x2)>>1;
			elseif (($P*8)%8 == 7) $COLOR[1] = ($COLOR[1] & 0x1);
			$COLOR[1] = $PALETTE[$COLOR[1]+1];
		 }
		 else
			return FALSE;
		 imagesetpixel($res,$X,$Y,$COLOR[1]);
		 $X++;
		 $P += $BMP['bytes_per_pixel'];
		}
		$Y--;
		$P+=$BMP['decal'];
	   }
	   fclose($f1);

	 return $res;
	}

	function createimage($type, $source_pic) {
		$image = false;
		switch($type) {
			case 'jpg':
			case 'jpeg':
				if(!$image = @imagecreatefromjpeg($source_pic)) {
					unlink($source_pic);
					error(ERR_INVALIDIMG);
				}
				break;
			case 'png':
				if(!$image = @imagecreatefrompng($source_pic)) {
					unlink($source_pic);
					error(ERR_INVALIDIMG);
				}
				break;
			case 'gif':
				if(!$image = @imagecreatefromgif($source_pic)) {
					unlink($source_pic);
					error(ERR_INVALIDIMG);
				}
				break;
			case 'bmp':
				if(!$image = @imagecreatefrombmp($source_pic)) {
					unlink($source_pic);
					error(ERR_INVALIDIMG);
				}
				break;
			default:
				error('Unknwon file extension.');
		}
		return $image;
	}
	
	function resize($src, $width, $height, $destination_pic, $max_width, $max_height) {
		$return = Array();

		$x_ratio = $max_width / $width;
		$y_ratio = $max_height / $height;

		if(($width <= $max_width) && ($height <= $max_height)) {
			$tn_width = $width;
			$tn_height = $height;
			} elseif (($x_ratio * $height) < $max_height) {
				$tn_height = ceil($x_ratio * $height);
				$tn_width = $max_width;
			} else {
				$tn_width = ceil($y_ratio * $width);
				$tn_height = $max_height;
		}
		
		$return['width'] = $tn_width;
		$return['height'] = $tn_height;
		
		$tmp = imagecreatetruecolor($tn_width, $tn_height);
		imagecolortransparent($tmp, imagecolorallocatealpha($tmp, 0, 0, 0, 0));
		imagealphablending($tmp, false);
		imagesavealpha($tmp, true);
		
		imagecopyresampled($tmp, $src, 0, 0, 0, 0, $tn_width, $tn_height, $width, $height);
		
		imagepng($tmp, $destination_pic, 4);
		imagedestroy($src);
		imagedestroy($tmp);
		
		return $return;
	}
	
	function imagebmp(&$img, $filename='') {
		$widthOrig = imagesx($img);
		$widthFloor = ((floor($widthOrig/16))*16);
		$widthCeil = ((ceil($widthOrig/16))*16);
		$height = imagesy($img);
		
		$size = ($widthCeil*$height*3)+54;
		
		// Bitmap File Header
		$result = 'BM';	 // header (2b)
		$result .= int_to_dword($size); // size of file (4b)
		$result .= int_to_dword(0); // reserved (4b)
		$result .= int_to_dword(54); // byte location in the file which is first byte of IMAGE (4b)
		// Bitmap Info Header
		$result .= int_to_dword(40); // Size of BITMAPINFOHEADER (4b)
		$result .= int_to_dword($widthCeil); // width of bitmap (4b)
		$result .= int_to_dword($height); // height of bitmap (4b)
		$result .= int_to_word(1);	// biPlanes = 1 (2b)
		$result .= int_to_word(24); // biBitCount = {1 (mono) or 4 (16 clr ) or 8 (256 clr) or 24 (16 Mil)} (2b
		$result .= int_to_dword(0); // RLE COMPRESSION (4b)
		$result .= int_to_dword(0); // width x height (4b)
		$result .= int_to_dword(0); // biXPelsPerMeter (4b)
		$result .= int_to_dword(0); // biYPelsPerMeter (4b)
		$result .= int_to_dword(0); // Number of palettes used (4b)
		$result .= int_to_dword(0); // Number of important colour (4b)
		
		// is faster than chr()
		$arrChr = array();
		for($i=0; $i<256; $i++){
		$arrChr[$i] = chr($i);
		}
		
		// creates image data
		$bgfillcolor = array('red'=>0, 'green'=>0, 'blue'=>0);
		
		// bottom to top - left to right - attention blue green red !!!
		$y=$height-1;
		for ($y2=0; $y2<$height; $y2++) {
			for ($x=0; $x<$widthFloor;	) {
				$rgb = imagecolorsforindex($img, imagecolorat($img, $x++, $y));
				$result .= $arrChr[$rgb['blue']].$arrChr[$rgb['green']].$arrChr[$rgb['red']];
				$rgb = imagecolorsforindex($img, imagecolorat($img, $x++, $y));
				$result .= $arrChr[$rgb['blue']].$arrChr[$rgb['green']].$arrChr[$rgb['red']];
				$rgb = imagecolorsforindex($img, imagecolorat($img, $x++, $y));
				$result .= $arrChr[$rgb['blue']].$arrChr[$rgb['green']].$arrChr[$rgb['red']];
				$rgb = imagecolorsforindex($img, imagecolorat($img, $x++, $y));
				$result .= $arrChr[$rgb['blue']].$arrChr[$rgb['green']].$arrChr[$rgb['red']];
				$rgb = imagecolorsforindex($img, imagecolorat($img, $x++, $y));
				$result .= $arrChr[$rgb['blue']].$arrChr[$rgb['green']].$arrChr[$rgb['red']];
				$rgb = imagecolorsforindex($img, imagecolorat($img, $x++, $y));
				$result .= $arrChr[$rgb['blue']].$arrChr[$rgb['green']].$arrChr[$rgb['red']];
				$rgb = imagecolorsforindex($img, imagecolorat($img, $x++, $y));
				$result .= $arrChr[$rgb['blue']].$arrChr[$rgb['green']].$arrChr[$rgb['red']];
				$rgb = imagecolorsforindex($img, imagecolorat($img, $x++, $y));
				$result .= $arrChr[$rgb['blue']].$arrChr[$rgb['green']].$arrChr[$rgb['red']];
			}
			for ($x=$widthFloor; $x<$widthCeil; $x++) {
				$rgb = ($x<$widthOrig) ? imagecolorsforindex($img, imagecolorat($img, $x, $y)) : $bgfillcolor;
				$result .= $arrChr[$rgb['blue']].$arrChr[$rgb['green']].$arrChr[$rgb['red']];
			}
			$y--;
		}
		
		// see imagegif
		if($filename == '') {
			echo $result;
		} else {
			$file = fopen($filename, 'wb');
			fwrite($file, $result);
			fclose($file);
		}
	}
	// imagebmp helpers
	function int_to_dword($n) {
		return chr($n & 255).chr(($n >> 8) & 255).chr(($n >> 16) & 255).chr(($n >> 24) & 255);
	}
	function int_to_word($n) {
		return chr($n & 255).chr(($n >> 8) & 255);
	}
?>

