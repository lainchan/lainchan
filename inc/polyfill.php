<?php

// PHP 5.4

if (!function_exists('hex2bin')) {
	function hex2bin($data) {
		$hex_string = null;
  return pack("H*" , $hex_string);
	}
}

// PHP 5.6

if (!function_exists('hash_equals')) {
	function hash_equals($ours, $theirs) {
		$ours = (string)$ours;
		$theirs = (string)$theirs;

		$tlen = strlen($theirs);
		$olen = strlen($ours);

		$answer = 0;
		for ($i = 0; $i < $tlen; $i++) {
			$answer |= ord($ours[$olen > $i ? $i : 0]) ^ ord($theirs[$i]);
		}

		return $answer === 0 && $olen === $tlen;
	}
}

if (!function_exists('imagecreatefrombmp')) {
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
		$BMP['colors'] = 2 ** $BMP['bits_per_pixel'];
		if ($BMP['size_bitmap'] == 0) $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
		$BMP['bytes_per_pixel'] = $BMP['bits_per_pixel']/8;
		$BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']);
		$BMP['decal'] = ($BMP['width']*$BMP['bytes_per_pixel']/4);
		$BMP['decal'] -= floor($BMP['width']*$BMP['bytes_per_pixel']/4);
		$BMP['decal'] = 4-(4*$BMP['decal']);
		if ($BMP['decal'] == 4) $BMP['decal'] = 0;
		$PALETTE = [];
		if ($BMP['colors'] < 16_777_216)
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
}

if (!function_exists('imagebmp')) {
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
		$arrChr = [];
		for ($i=0; $i<256; $i++){
		$arrChr[$i] = chr($i);
		}
		// creates image data
		$bgfillcolor = ['red'=>0, 'green'=>0, 'blue'=>0];
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
		if ($filename == '') {
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
}
